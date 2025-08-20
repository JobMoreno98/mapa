import pandas as pd
import json
from itertools import combinations

# Leer el archivo
archivo_excel = 'aulas.xlsx'  # Cambia esto por tu archivo
df = pd.read_excel(archivo_excel)

df.columns = df.columns.str.strip()

# Coordenadas por edificio (ejemplo, pon las reales)
coordenadas_edificios = {
    "Edificio A": {"lat": 20.740475484548387,  "lng": -103.37718367128555},
    "Edificio B": {"lat": 20.740711386029997,  "lng": -103.37715332555685},
    "Edificio C": {"lat": 20.740946568688557, "lng":  -103.37713646203734},
    "Edificio D": {"lat": 20.74126051171166, "lng": -103.37717060097611},
    "Edificio F4": {"lat": 20.741444975105047,  "lng": -103.37713266880324},
    "Edificio G": {"lat": 20.74193273779499, "lng":  -103.37581831942917},
    "Edificio H": {"lat": 20.741629438270856, "lng":  -103.37594349556005},
    "Edificio I": {"lat": 20.7413296855122, "lng":  -103.3760686716909},
    # Completa con todos tus edificios
}

# Crear nodos con coordenadas del edificio
nodes = []
for _, row in df.iterrows():
    edificio = row['Edificio']
    coords = coordenadas_edificios.get(edificio, {"lat": None, "lng": None})
    node = {
        "id": row['Área'],
        "label": row['Área'],
        "sede": row['Sede'],
        "edificio": edificio,
        "piso": row['Piso'],
        "lat": coords['lat'],
        "lng": coords['lng']
    }
    nodes.append(node)

# Crear edges entre áreas del mismo edificio y piso
edges = []
grouped = df.groupby(['Sede', 'Edificio', 'Piso'])

for _, group in grouped:
    areas = group['Área'].tolist()
    for a, b in combinations(areas, 2):
        edges.append({"from": a, "to": b})
        edges.append({"from": b, "to": a})  # Para hacer bidireccional

# Guardar grafo
graph = {
    "nodes": nodes,
    "edges": edges
}

with open('grafo_leaflet.json', 'w', encoding='utf-8') as f:
    json.dump(graph, f, ensure_ascii=False, indent=2)

print("✅ grafo_leaflet.json creado con coordenadas.")
