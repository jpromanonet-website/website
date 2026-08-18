import json
import re
import urllib.request

ua = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
url = "https://jpromanonet.medium.com/"
req = urllib.request.Request(url, headers={"User-Agent": ua})
with urllib.request.urlopen(req, timeout=30) as r:
    html = r.read().decode("utf-8", "replace")
    cookies = r.headers.get_all("Set-Cookie") if hasattr(r.headers, "get_all") else []

# Extract PRELOADED_STATE
m = re.search(r"window\.__PRELOADED_STATE__\s*=\s*(\{.*?\})\s*</script>", html, re.S)
print("preloaded match", bool(m))
if m:
    raw = m.group(1)
    # Sometimes ends with ; 
    raw = raw.rstrip(";")
    try:
        data = json.loads(raw)
        print("keys", list(data.keys())[:20])
        Path = __import__("pathlib").Path
        Path(r"C:\Users\Usuario\AppData\Local\Temp\medium-preloaded.json").write_text(json.dumps(data)[:50000], encoding="utf-8")
    except Exception as e:
        print("json err", e)
        print(raw[:500])

# Apollo state
m2 = re.search(r"__APOLLO_STATE__\s*=\s*(\{.*?\})\s*(?:</script>|window\.)", html, re.S)
print("apollo match", bool(m2))
if not m2:
    # try alternate
    idx = html.find("__APOLLO_STATE__")
    print("apollo idx", idx)
    print(html[idx:idx+300])

# Find user id
uids = re.findall(r'"id":"(user_[^"]+|[^"]{10,})"', html)
print("id samples", uids[:10])
uids2 = re.findall(r'"__typename":"User"[^}]*?"id":"([^"]+)"', html)
print("user typename ids", uids2[:5])
# medium user ids are often like this
uids3 = re.findall(r'"authorId":"([^"]+)"', html)
print("authorIds", list(dict.fromkeys(uids3))[:5])
