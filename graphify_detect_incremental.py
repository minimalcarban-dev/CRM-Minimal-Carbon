import sys, json
from graphify.detect import detect_incremental, save_manifest
from pathlib import Path

try:
    result = detect_incremental(Path("."))
    new_total = result.get("new_total", 0)
    output_path = Path("graphify-out/.graphify_incremental.json")
    output_path.parent.mkdir(parents=True, exist_ok=True)
    output_path.write_text(json.dumps(result))
    print(json.dumps(result))
except Exception as e:
    print(json.dumps({"error": str(e)}))
