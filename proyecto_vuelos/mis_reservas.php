<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: ./login.html');
  exit;
}
$esAdmin = ($_SESSION['rol'] ?? '') === 'admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mis Reservas</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="header">
    <div class="header-content" style="justify-content:space-between;max-width:1200px;margin:0 auto;padding:0 1rem;">
      <div style="display:flex;align-items:center;gap:1rem;">
        <i class="fas fa-ticket"></i>
        <h1>Mis Reservas</h1>
      </div>
      <div style="display:flex;align-items:center;gap:0.75rem;">
        <a class="btn-small" href="index.html" style="text-decoration:none;"><i class="fas fa-plane-departure"></i> Nueva reserva</a>
        <?php if ($esAdmin) { ?>
          <a class="btn-small" href="admin.php" style="text-decoration:none;"><i class="fas fa-cog"></i> Admin</a>
        <?php } ?>
        <span style="font-weight:500;opacity:.9;">Hola, <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></span>
        <button class="btn-small delete" id="btn-logout" title="Cerrar sesión" style="border:none;">
          <i class="fas fa-right-from-bracket"></i> Salir
        </button>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="table-wrapper">
      <table id="tabla-reservas">
        <thead>
          <tr>
            <th>ID</th>
            <th>Pasajero</th>
            <th>Documento</th>
            <th>Origen</th>
            <th>Destino</th>
            <th>Fecha Ida</th>
            <th>Fecha Vuelta</th>
            <th>Personas</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
      <div id="mensaje-vacio" style="display:none;padding:1rem;text-align:center;color:#4a5568;">No tienes reservas aún.</div>
    </div>
  </div>

  <script>
    function mostrarMensajeExito(mensaje) {
      const toast = document.createElement('div');
      toast.className = 'toast success';
      toast.innerHTML = `<i class="fas fa-check-circle"></i><span>${mensaje}</span>`;
      document.body.appendChild(toast);
      setTimeout(() => toast.remove(), 3000);
    }
    function mostrarMensajeError(mensaje) {
      const toast = document.createElement('div');
      toast.className = 'toast error';
      toast.innerHTML = `<i class="fas fa-exclamation-circle"></i><span>${mensaje}</span>`;
      document.body.appendChild(toast);
      setTimeout(() => toast.remove(), 3000);
    }

    function formatearFecha(fecha) {
      if (!fecha) return '-';
      const f = new Date(fecha);
      if (isNaN(f.getTime())) return fecha;
      return f.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    async function cargarMisReservas() {
      try {
        const resp = await fetch('./api/mis_reservas.php');
        if (resp.status === 401) {
          window.location.href = './login.html';
          return;
        }
        if (!resp.ok) throw new Error('No se pudo cargar');
        const reservas = await resp.json();
        const tbody = document.querySelector('#tabla-reservas tbody');
        tbody.innerHTML = '';
        const vacio = document.getElementById('mensaje-vacio');
        if (!reservas || reservas.length === 0) {
          vacio.style.display = 'block';
          return;
        } else {
          vacio.style.display = 'none';
        }
        reservas.forEach(r => {
          const tr = document.createElement('tr');
          tr.className = `estado-${r.estado}`;
          tr.innerHTML = `
            <td>${r.id_reservas}</td>
            <td>${r.nombre_pasajero}</td>
            <td>${r.documento_pasajero}</td>
            <td>${r.origen}</td>
            <td>${r.destino}</td>
            <td>${formatearFecha(r.fecha_ida)}</td>
            <td>${formatearFecha(r.fecha_vuelta)}</td>
            <td>${r.personas}</td>
            <td class="estado-cell">${r.estado}</td>
          `;
          tbody.appendChild(tr);
        });
      } catch (err) {
        mostrarMensajeError('Error al cargar tus reservas');
      }
    }

    document.getElementById('btn-logout').addEventListener('click', async () => {
      try {
        const resp = await fetch('./api/logout.php');
        await resp.json();
      } catch {}
      window.location.href = './login.html';
    });

    cargarMisReservas();
  </script>
</body>
</html>
