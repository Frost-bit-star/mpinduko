const express = require("express");
const fetch = require("node-fetch");
const cors = require("cors");
const path = require("path");

const app = express();
app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, "public")));

const PORT = process.env.PORT || 3000;

// 1. SEARCH ENDPOINT
app.get("/search", async (req, res) => {
  const { q } = req.query;
  if (!q) return res.status(400).json({ error: "No query provided" });

  try {
    const response = await fetch(
      `https://apis.davidcyriltech.my.id/search/xvideo?text=${encodeURIComponent(q)}`
    );
    const data = await response.json();

    // Rewrite video URLs to our /stream endpoint
    if (data.result) {
      data.result = data.result.map((v) => ({
        ...v,
        download_url: `/stream?url=${encodeURIComponent(v.download_url)}`,
      }));
    }

    res.json(data);
  } catch (err) {
    console.error("Search Error:", err);
    res.status(500).json({ error: "Failed to fetch search results" });
  }
});

// 2. VIDEO DETAILS ENDPOINT
app.get("/video", async (req, res) => {
  const { url } = req.query;
  if (!url) return res.status(400).json({ error: "No video URL provided" });

  try {
    const response = await fetch(
      `https://apis.davidcyriltech.my.id/xvideo?url=${encodeURIComponent(url)}`
    );
    const data = await response.json();
    res.json(data);
  } catch (err) {
    console.error("Video Fetch Error:", err);
    res.status(500).json({ error: "Failed to fetch video info" });
  }
});

// 3. STREAM VIDEO FOR HTML5 PLAYER
app.get("/stream", async (req, res) => {
  const { url } = req.query;
  if (!url) return res.status(400).json({ error: "No URL provided" });

  try {
    const headers = {};
    if (req.headers.range) {
      headers.Range = req.headers.range;
    }

    const videoResponse = await fetch(url, { headers });
    if (!videoResponse.ok) throw new Error("Failed to fetch video stream");

    // Forward essential headers for video streaming
    videoResponse.headers.forEach((value, key) => {
      if (
        ["content-type", "content-length", "content-range", "accept-ranges"].includes(
          key.toLowerCase()
        )
      ) {
        res.setHeader(key, value);
      }
    });

    res.setHeader("Access-Control-Allow-Origin", "*");

    videoResponse.body.pipe(res);
  } catch (err) {
    console.error("Stream Error:", err);
    res.status(500).json({ error: "Failed to stream video" });
  }
});

// 4. DOWNLOAD VIDEO ENDPOINT
app.get("/download", async (req, res) => {
  const { url, title } = req.query;
  if (!url || !title) return res.status(400).json({ error: "No URL or title provided" });

  try {
    // Fetch direct download link
    const apiResponse = await fetch(
      `https://apis-keith.vercel.app/download/porn?url=${encodeURIComponent(url)}`
    );
    const data = await apiResponse.json();

    if (!data?.result?.url) throw new Error("Failed to get download link");

    const downloadUrl = data.result.url;
    const safeTitle = title.replace(/[^a-zA-Z0-9]/g, "_") + ".mp4";

    const videoResponse = await fetch(downloadUrl);
    if (!videoResponse.ok) throw new Error("Failed to fetch video for download");

    res.setHeader("Content-Disposition", `attachment; filename="${safeTitle}"`);
    res.setHeader("Content-Type", "video/mp4");
    videoResponse.body.pipe(res);
  } catch (err) {
    console.error("Download Error:", err);
    res.status(500).json({ error: "Failed to download video" });
  }
});

app.listen(PORT, () => console.log(`Server running on port ${PORT}`));
