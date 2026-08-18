import json
import urllib.error
import urllib.request

UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
USER_ID = "768cb0ffbcaf"

def req(url, data=None, headers=None):
    h = {"User-Agent": UA, "Accept": "application/json"}
    if headers:
        h.update(headers)
    body = None
    if data is not None:
        body = data if isinstance(data, (bytes, bytearray)) else json.dumps(data).encode()
        h.setdefault("Content-Type", "application/json")
    request = urllib.request.Request(url, data=body, headers=h)
    try:
        with urllib.request.urlopen(request, timeout=30) as r:
            raw = r.read()
            return r.status, raw
    except urllib.error.HTTPError as e:
        return e.code, e.read()

endpoints = [
    f"https://medium.com/_/api/users/{USER_ID}/profile/stream?limit=100&source=latest",
    f"https://medium.com/_/api/users/{USER_ID}/profile/stream?limit=25",
    f"https://medium.com/_/api/users/{USER_ID}/latest",
    "https://medium.com/_/api/users/jpromanonet/profile/stream?limit=25",
]

for url in endpoints:
    code, raw = req(url)
    text = raw.decode("utf-8", "replace")
    print("\n===", code, url)
    print(text[:400])

# GraphQL homepagePostsConnection
query = [{
    "operationName": "UserProfileQuery",
    "variables": {"username": "jpromanonet", "homepagePostsFrom": None, "includeDistributedResponses": True},
    "query": """
query UserProfileQuery($username: String!, $homepagePostsFrom: String, $includeDistributedResponses: Boolean) {
  user(username: $username) {
    id
    name
    username
    homepagePostsConnection(paging: {limit: 25, from: $homepagePostsFrom}, includeDistributedResponses: $includeDistributedResponses) {
      posts {
        id
        title
        uniqueSlug
        firstPublishedAt
        latestPublishedAt
        mediumUrl
        clapCount
      }
      pagingInfo {
        next { from limit }
      }
    }
  }
}
"""
}]
code, raw = req("https://medium.com/_/graphql", query, {"Content-Type": "application/json", "Accept": "*/*", "Origin": "https://medium.com", "Referer": "https://medium.com/@jpromanonet"})
print("\n=== GQL", code)
print(raw.decode("utf-8", "replace")[:800])
