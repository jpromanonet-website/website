import json
import time
import urllib.error
import urllib.request
from pathlib import Path

UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
USER_ID = "768cb0ffbcaf"
USERNAME = "jpromanonet"


def fetch_stream(to=None, limit=100):
    url = f"https://medium.com/_/api/users/{USER_ID}/profile/stream?limit={limit}&source=latest"
    if to is not None:
        url += f"&to={to}"
    req = urllib.request.Request(url, headers={"User-Agent": UA, "Accept": "application/json"})
    with urllib.request.urlopen(req, timeout=30) as r:
        raw = r.read().decode("utf-8", "replace")
    if raw.startswith("])}while(1);</x>"):
        raw = raw[len("])}while(1);</x>"):]
    return json.loads(raw)


def normalize_posts(payload):
    posts = []
    refs = payload.get("references", {}).get("Post", {})
    for post in refs.values():
        title = (post.get("title") or "").strip()
        slug = (post.get("uniqueSlug") or "").strip()
        if not title or not slug:
            continue
        ts = int(post.get("firstPublishedAt") or post.get("latestPublishedAt") or 0)
        # ms to seconds if needed
        if ts > 10_000_000_000:
            ts = ts // 1000
        posts.append(
            {
                "title": title,
                "url": f"https://{USERNAME}.medium.com/{slug}",
                "category": "Medium",
                "pubDate": time.strftime("%a, %d %b %Y %H:%M:%S GMT", time.gmtime(ts)) if ts else "",
                "timestamp": ts,
                "source": "medium",
                "imageSrc": "medium.svg",
            }
        )
    return posts


all_by_url = {}
to = None
for page in range(1, 20):
    try:
        data = fetch_stream(to=to, limit=100)
    except urllib.error.HTTPError as e:
        print("HTTP error", e.code, "page", page)
        break
    payload = data.get("payload", {})
    batch = normalize_posts(payload)
    print(f"page {page}: {len(batch)} posts, to={to}")
    for p in batch:
        all_by_url[p["url"]] = p
    nxt = (payload.get("paging") or {}).get("next") or {}
    new_to = nxt.get("to")
    if not new_to or new_to == to:
        break
    to = new_to
    time.sleep(0.4)

posts = sorted(all_by_url.values(), key=lambda p: p["timestamp"], reverse=True)
out = Path(r"C:\Users\Usuario\Documents\Git\jpromanonet\website\assets\data\medium-archive.json")
out.write_text(json.dumps(posts, ensure_ascii=False, indent=2), encoding="utf-8")
print("TOTAL", len(posts), "->", out)
for p in posts[:5]:
    print("-", p["title"])
print("...")
for p in posts[-3:]:
    print("-", p["title"])
