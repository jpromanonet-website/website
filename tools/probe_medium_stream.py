import json
import urllib.request

UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
USER_ID = "768cb0ffbcaf"

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

data = fetch_stream()
print("top keys", data.keys())
payload = data.get("payload", {})
print("payload keys", payload.keys())
stream = payload.get("streamItems") or payload.get("userStream") or payload
# explore structure
print(json.dumps({k: (type(v).__name__, (len(v) if hasattr(v,'__len__') and not isinstance(v,str) else str(v)[:80])) for k,v in payload.items()}, indent=2)[:2000])

# Find posts references
references = payload.get("references", {})
print("ref keys", references.keys() if isinstance(references, dict) else None)
posts = references.get("Post", {}) if isinstance(references, dict) else {}
print("posts count", len(posts))
for i, (pid, post) in enumerate(posts.items()):
    title = post.get("title")
    slug = post.get("uniqueSlug")
    print(i+1, title, slug)
    if i >= 14:
        print("...")
        break

# paging?
print("paging", payload.get("paging"))
print("next", payload.get("nextTo"))
print("stream len", len(payload.get("streamItems", [])))
