import json
import re
import urllib.request

ua = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"

def fetch(url: str) -> tuple[int, str]:
    req = urllib.request.Request(url, headers={"User-Agent": ua, "Accept": "text/html,application/json"})
    try:
        with urllib.request.urlopen(req, timeout=25) as r:
            return r.status, r.read().decode("utf-8", "replace")
    except Exception as e:
        return 0, str(e)

for url in [
    "https://jpromanonet.medium.com/",
    "https://medium.com/@jpromanonet",
    "https://medium.com/feed/@jpromanonet",
]:
    code, body = fetch(url)
    print(url, "->", code, "len", len(body))
    links = set(re.findall(r"https://jpromanonet\.medium\.com/[a-z0-9\-]+-[a-f0-9]{8,}", body, re.I))
    print("  links", len(links))
    if links:
        print("  sample", list(links)[:3])
