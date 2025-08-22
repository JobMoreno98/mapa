@extends('layout.app')

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-color-markers/dist/leaflet-color-markers.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        #map {
            height: 600px;
        }

        #controls {
            margin: 10px 0;
        }

        #instructions {
            padding: 15px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            border-radius: 8px;
            font-family: sans-serif;
        }

        .leaflet-bottom {
            display: none;
        }
    </style>
@endsection
@section('cuerpo')
    <div class="container-fluid d-flex flex-column justify-content-center align-content-center flex-wrap">
        <h2>Calcula rutas en el mapa</h2>
        <div id="controls" class="col-sm-12 col-md-3 d-flex flex-column">
            <label class="form-label" for="startSelect">Inicio:</label>
            <select class="form-control" id="startSelect"></select>
            <label class="form-label" for="endSelect">Destino:</label>
            <select class="form-control" id="endSelect"></select>
            <button class="btn btn-success btn-sm my-1" id="routeBtn">
                Calcular ruta
            </button>
        </div>
        <div class="w-100 d-flex flex-column flex-md-row">
            <div class="order-1 order-md-2 col-12 col-md-3 m-1" id="instructions">
                Instrucciones
            </div>

            <div class="order-2 order-md-1 col-12 col-md-8 m-1" id="map"></div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const redIcon = new L.Icon({
            iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png",
            shadowUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png",
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41],
        });

        let map = L.map("map").setView([20.7406, -103.3771], 17);
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 19,
            attribution: "© OpenStreetMap",
        }).addTo(map);

        let nodes = [];
        let edges = [];
        let markers = {};
        let routeLayer = null;
        fetch("{{ route('all.nodes') }}") // <- crea una ruta en Laravel que devuelva todos los nodos
            .then(r => r.json())
            .then(data => {
                nodes = data; // Guardamos los nodos para uso posterior

                nodes.forEach(n => {
                    // Mostrar solo los nodos relevantes por defecto
                    if (!markers[n.nombre]) {
                        if (n.nombre.startsWith("Puerta") || n.nombre.startsWith("Entrada")) {
                            markers[n.nombre] = L.marker([n.lat, n.lng]).addTo(map).bindPopup(n.nombre);
                        } else {
                            markers[n.inombred] = {
                                getLatLng: () => L.latLng(n.lat, n.lng),
                            };
                        }
                    }

                    // Llenar los selects
                    let opt = document.createElement("option");
                    opt.value = n.nombre;
                    opt.textContent = n.nombre;
                    startSelect.appendChild(opt.cloneNode(true));
                    endSelect.appendChild(opt);
                });
            });

        document.getElementById("routeBtn").addEventListener("click", () => {
            // Limpiar ruta anterior
            if (routeLayer) map.removeLayer(routeLayer);
            if (markers.start) map.removeLayer(markers.start);
            if (markers.end) map.removeLayer(markers.end);

            const startId = document.getElementById("startSelect").value;
            const endId = document.getElementById("endSelect").value;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch("{{ route('ruta') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": token
                    },
                    body: JSON.stringify({
                        start: startId,
                        end: endId
                    })
                })
                .then(r => r.json())
                .then(data => {
                    // Mostrar nodos si no estaban antes
                    console.log(data);
                    data.nodes.forEach(n => {
                        if (!markers[n.nombre]) {
                            markers[n.nombre] = L.marker([n.lat, n.lng]).addTo(map).bindPopup(n.nombre);
                        }
                    });

                    // Marcar inicio y fin
                    const startNode = data.nodes.find(n => n.nombre === startId);
                    const endNode = data.nodes.find(n => n.nombre === endId);
                    markers.start = L.marker([startNode.lat, startNode.lng], {
                            icon: redIcon
                        })
                        .addTo(map).bindPopup(startId);
                    markers.end = L.marker([endNode.lat, endNode.lng], {
                            icon: redIcon
                        })
                        .addTo(map).bindPopup(endId);

                    // Dibujar ruta
                    const latlngs = data.path.map(id => {
                        const node = data.nodes.find(n => n.nombre === id);
                        return [node.lat, node.lng];
                    });

                    routeLayer = L.polyline(latlngs, {
                        color: "blue",
                        weight: 4
                    }).addTo(map);
                    map.fitBounds(routeLayer.getBounds());

                    // Instrucciones
                    document.getElementById("instructions").innerHTML =
                        generarInstrucciones(data.path, data.nodes);
                });
        });


        function generarInstrucciones(path, nodes) {
            const pisoANumero = (p) => {
                if (!p) return 0;
                if (p === "PB") return 0;
                return parseInt(p.replace("P", ""));
            };

            const findNode = (id) => nodes.find(n => n.nombre === id);

            let instruccionesHTML = "<h4>Instrucciones:</h4><ol>";
            instruccionesHTML += `<li>Comienza en <strong>${path[0]}</strong>.</li>`;

            for (let i = 0; i < path.length - 1; i++) {
                const prev = i > 0 ? findNode(path[i - 1]) : null;
                const curr = findNode(path[i]);
                const next = findNode(path[i + 1]);

                if (!curr || !next) {
                    console.warn("Nodo no encontrado:", path[i]);
                    continue;
                }

                // Movimiento vertical
                const pisoCurr = pisoANumero(curr.piso);
                const pisoNext = pisoANumero(next.piso);

                if (curr.piso && next.piso && pisoCurr !== pisoNext) {
                    if (pisoNext > pisoCurr) {
                        instruccionesHTML +=
                            `<li>Sube a la planta <strong>${next.piso}</strong> por las escaleras. ⬆️</li>`;
                    } else {
                        instruccionesHTML +=
                            `<li>Baja a la planta <strong>${next.piso}</strong> por las escaleras. ⬇️</li>`;
                    }
                    continue;
                }

                // Dirección de giro (solo si hay nodo previo)
                if (prev) {
                    const dir = calcularDireccion(prev, curr, next);
                    if (dir === "izquierda") {
                        instruccionesHTML += `<li>Gira a la izquierda hacia <strong>${next.nombre}</strong>. ↩️</li>`;
                    } else if (dir === "derecha") {
                        instruccionesHTML += `<li>Gira a la derecha hacia <strong>${next.nombre}</strong>. ↪️</li>`;
                    } else {
                        instruccionesHTML += `<li>Avanza hacia <strong>${next.nombre}</strong>. ➡️</li>`;
                    }
                } else {
                    // Primera instrucción después del inicio
                    instruccionesHTML += `<li>Avanza hacia <strong>${next.nombre}</strong>. ➡️</li>`;
                }
            }

            instruccionesHTML += `<li>Ya llegaste a tu destino: <strong>${path[path.length - 1]}</strong>. 🏁</li>`;
            instruccionesHTML += "</ol>";

            return instruccionesHTML;
        }

        function calcularDireccion(prev, curr, next) {
            const a = [curr.lng - prev.lng, curr.lat - prev.lat];
            const b = [next.lng - curr.lng, next.lat - curr.lat];
            const cruz = a[0] * b[1] - a[1] * b[0];

            if (cruz > 0.00001) return "izquierda";
            if (cruz < -0.00001) return "derecha";
            return "recto";
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#startSelect').select2();
            $('#endSelect').select2();
        });
    </script>
@endsection
