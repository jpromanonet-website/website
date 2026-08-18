import json
import re
import urllib.request
from html import unescape

ua = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
url = "https://jpromanonet.medium.com/"
req = urllib.request.Request(url, headers={"User-Agent": ua})
with urllib.request.urlopen(req, timeout=30) as r:
    html = r.read().decode("utf-8", "replace")

Path = __import__("pathlib").Path
Path(r"C:\Users\Usuario\AppData\Local\Temp\medium-profile.html").write_text(html, encoding="utf-8")
print("saved", len(html))

# Common Medium embed patterns
patterns = [
    r'"title":"(.*?)"',
    r'https://jpromanonet\.medium\.com/[^"\\]+',
    r'/@jpromanonet/[a-z0-9\-]+-[a-f0-9]{8,}',
    r'"uniqueSlug":"([^"]+)"',
    r'"slug":"([^"]+)"',
    r'Post:[a-f0-9]+',
]
for p in patterns:
    m = re.findall(p, html, re.I)
    uniq = list(dict.fromkeys(m))
    print(p, "->", len(uniq))
    print("  ", uniq[:5])

# Look for apollo/graphql state
for marker in ["__APOLLO_STATE__", "window.__PRELOADED_STATE__", "__NEXT_DATA__", "graphql"]:
    print(marker, marker in html)

# Extract article-ish anchors
hrefs = re.findall(r'href="([^"]+)"', html)
med = [h for h in hrefs if "medium.com" in h or h.startswith("/@") or h.startswith("/")]
print("href sample", med[:30])
