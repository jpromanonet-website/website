import re
from pathlib import Path
import urllib.request

html = urllib.request.urlopen("http://192.168.100.50/jpromanonet/writing/").read().decode("utf-8", "replace")
Path(r"C:\Users\Usuario\AppData\Local\Temp\jpr-w5.html").write_text(html, encoding="utf-8")
metas = re.findall(r'catalog-item__meta">(.*?)</span>', html)
titles = re.findall(r'catalog-item__title">(.*?)</h2>', html)
print("articles", len(titles))
print("medium", metas.count("Medium"))
print("first", metas[:3])
print("last", metas[-3:])
from collections import Counter
dups = [t for t, n in Counter(titles).items() if n > 1]
print("dups", len(dups), dups[:5])
count = re.search(r'catalog-count[^>]*>(.*?)</p>', html)
print("count_label", count.group(1) if count else None)
