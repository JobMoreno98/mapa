@extends('layout.app')
@section('cuerpo')
    <div class="card my-3">
        <div class="card-header bg-warning text-dark">
            Editar Nodo
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="editSelect" class="form-label">Selecciona un nodo:</label>
                <select class="form-select" id="editSelect">

                </select>
            </div>
            <div class="mb-3">
                <label for="editLat" class="form-label">Latitud:</label>
                <input type="number" class="form-control" id="editLat" step="any" required>
            </div>
            <div class="mb-3">
                <label for="editLng" class="form-label">Longitud:</label>
                <input type="number" class="form-control" id="editLng" step="any" required>
            </div>
            <button id="saveEditBtn" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </div>
@endsection
@section('js')
    <script>
        // Cargar nodos en el selector al inicio (puedes usar los mismos datos del fetch inicial)
        fetch("{{ route('all.nodes') }}")
            .then(res => res.json())
            .then(nodos => {
                const select = document.getElementById("editSelect");
                nodos.forEach(n => {
                    const option = document.createElement("option");
                    option.value = n.nombre;
                    option.textContent = n.nombre;
                    select.appendChild(option);
                });

                // Cuando se seleccione un nodo, mostrar su lat/lng actual
                select.addEventListener("change", () => {
                    const seleccionado = nodos.find(n => n.nombre === select.value);
                    if (seleccionado) {
                        document.getElementById("editLat").value = seleccionado.lat;
                        document.getElementById("editLng").value = seleccionado.lng;
                    }
                });

                // Trigger inicial
                select.dispatchEvent(new Event("change"));
            });

        // Guardar cambios del nodo
        document.getElementById("saveEditBtn").addEventListener("click", () => {
            const nombre = document.getElementById("editSelect").value;
            const lat = parseFloat(document.getElementById("editLat").value);
            const lng = parseFloat(document.getElementById("editLng").value);
            const token = document.querySelector('meta[name="csrf-token"]').content;

            fetch("{{ route('guarda.nodo') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": token,
                    },
                    body: JSON.stringify({
                        nombre,
                        lat,
                        lng
                    })
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message || "Nodo editado correctamente.");
                    // Aquí puedes refrescar marcadores o rutas si es necesario
                })
                .catch(err => {
                    console.error(err);
                    alert("Error al editar nodo.");
                });
        });
    </script>
@endsection
