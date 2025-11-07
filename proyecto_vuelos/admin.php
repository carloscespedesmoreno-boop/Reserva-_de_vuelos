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
  <title>Panel de Administración</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="header">
    <div class="header-content" style="justify-content:space-between;max-width:1200px;margin:0 auto;padding:0 1rem;">
      <div style="display:flex;align-items:center;gap:1rem;">
        <i class="fas fa-cog"></i>
        <h1><?php echo $esAdmin ? 'Panel de Administración' : 'Mis Reservas'; ?></h1>
      </div>
      <div style="display:flex;align-items:center;gap:0.75rem;">
        <span style="font-weight:500;opacity:.9;">Hola, <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?> 
          <?php if ($esAdmin) echo '<span style="font-size:0.8rem;background:#48bb78;padding:0.2rem 0.5rem;border-radius:0.3rem;margin-left:0.5rem;">Admin</span>'; ?>
        </span>
        <button class="btn-small delete" id="btn-logout" title="Cerrar sesión" style="border:none;">
          <i class="fas fa-right-from-bracket"></i> Salir
        </button>
      </div>
    </div>
  </div>

  <div class="container">
    <?php if (!$esAdmin) { ?>
      <div style="max-width:1200px;margin:0 auto 1rem auto;padding:0.75rem 1rem;background:#fff3cd;border:1px solid #ffeeba;border-radius:8px;color:#856404;">
        <i class="fas fa-info-circle"></i> Estás autenticado como usuario. Para aprobar o cancelar reservas, inicia sesión con un usuario administrador.
      </div>
    <?php } ?>
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
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <script>
    const esAdmin = <?php echo $esAdmin ? 'true' : 'false'; ?>;
    
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

    async function cargarReservas() {
      try {
  const resp = await fetch('./api/reservas.php', { credentials: 'same-origin' });
        if (!resp.ok) throw new Error('No se pudo cargar');
        const reservas = await resp.json();
        const tbody = document.querySelector('#tabla-reservas tbody');
        tbody.innerHTML = '';
        reservas.forEach(r => {
          const tr = document.createElement('tr');
          const estadoNormalizado = (r.estado || '').toLowerCase().trim();
          tr.dataset.reservaId = r.id_reservas;
          tr.className = `estado-${estadoNormalizado}`;

          const formatearFecha = (fecha) => {
            if (!fecha) return '-';
            const f = new Date(fecha);
            return f.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
          };

          let accionesHtml = '';
          if (esAdmin) {
            if (estadoNormalizado === 'pendiente') {
              accionesHtml = `
                <button class="btn-small approve" onclick="cambiarEstado(this, ${r.id_reservas}, 'aprobada')"><i class="fas fa-check"></i>Aprobar</button>
                <button class="btn-small cancel" onclick="cambiarEstado(this, ${r.id_reservas}, 'cancelada')"><i class="fas fa-times"></i>Cancelar</button>
                <button class="btn-small delete" onclick="eliminarReserva(this, ${r.id_reservas})"><i class="fas fa-trash"></i>Eliminar</button>
              `;
            } else if (estadoNormalizado === 'aprobada' || estadoNormalizado === 'cancelada') {
              accionesHtml = `
                <span class="estado-final">${estadoNormalizado === 'aprobada' ? '<i class="fas fa-check-circle"></i>Aprobada' : '<i class="fas fa-times-circle"></i>Cancelada'}</span>
                <button class="btn-small delete" onclick="eliminarReserva(this, ${r.id_reservas})"><i class="fas fa-trash"></i>Eliminar</button>
              `;
            } else {
              // Estado inesperado: mostrar opciones para recuperarlo
              accionesHtml = `
                <button class="btn-small approve" onclick="cambiarEstado(this, ${r.id_reservas}, 'aprobada')">Forzar Aprobar</button>
                <button class="btn-small cancel" onclick="cambiarEstado(this, ${r.id_reservas}, 'cancelada')">Forzar Cancelar</button>
                <button class="btn-small delete" onclick="eliminarReserva(this, ${r.id_reservas})">Eliminar</button>
              `;
              console.warn('Estado desconocido en reserva', r.id_reservas, r.estado);
            }
          }

          tr.innerHTML = `
            <td>${r.id_reservas}</td>
            <td>${r.nombre_pasajero}</td>
            <td>${r.documento_pasajero}</td>
            <td>${r.origen}</td>
            <td>${r.destino}</td>
            <td>${formatearFecha(r.fecha_ida)}</td>
            <td>${formatearFecha(r.fecha_vuelta)}</td>
            <td>${r.personas}</td>
            <td class="estado-cell">${estadoNormalizado}</td>
            <td class="action-buttons">${accionesHtml}</td>
          `;
          tbody.appendChild(tr);
        });
      } catch (err) {
        mostrarMensajeError('Error al cargar reservas');
      }
    }

    async function cambiarEstado(btnElement, id, estado) {
      try {
        const actionButtons = btnElement.parentElement.querySelectorAll('button');
        actionButtons.forEach(btn => { btn.disabled = true; btn.style.opacity = '0.5'; });
        const originalContent = btnElement.innerHTML;
        btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

        const resp = await fetch(`./api/reserva_id.php?id=${id}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ estado }),
          credentials: 'same-origin'
        });
        const data = await resp.json();
        if (resp.ok) {
          mostrarMensajeExito('Estado actualizado correctamente');
          const fila = btnElement.closest('tr');
          const estadoCell = fila.querySelector('.estado-cell');
          estadoCell.textContent = estado;
          const actionCell = btnElement.parentElement;
          actionCell.innerHTML = `
            <span class="estado-final">${estado === 'aprobada' ? '<i class="fas fa-check-circle"></i>Aprobada' : '<i class="fas fa-times-circle"></i>Cancelada'}</span>
            <button class="btn-small delete" onclick="eliminarReserva(this, ${id})"><i class="fas fa-trash"></i>Eliminar</button>
          `;
          fila.className = `estado-${estado}`;
        } else if (resp.status === 401) {
          mostrarMensajeError('Sesión expirada. Inicia sesión de nuevo.');
          setTimeout(() => (window.location.href = './login.html'), 1200);
        } else {
          mostrarMensajeError('Error: ' + (data.error || 'No se pudo actualizar'));
          const actionButtons2 = btnElement.parentElement.querySelectorAll('button');
          actionButtons2.forEach(btn => { btn.disabled = false; btn.style.opacity = '1'; });
          btnElement.innerHTML = originalContent;
        }
      } catch (err) {
        mostrarMensajeError('Error al conectar con el servidor');
      }
    }

    async function eliminarReserva(btnElement, id) {
      if (!confirm('¿Eliminar esta reserva?')) return;
      try {
        btnElement.disabled = true;
        const original = btnElement.innerHTML;
        btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
  const resp = await fetch(`./api/eliminar_reserva.php?id=${id}`, { method: 'DELETE', credentials: 'same-origin' });
        const data = await resp.json();
        if (resp.ok) {
          mostrarMensajeExito('Reserva eliminada');
          const fila = btnElement.closest('tr');
          fila.style.animation = 'fadeOut 0.5s ease';
          setTimeout(() => fila.remove(), 500);
        } else if (resp.status === 401) {
          mostrarMensajeError('Sesión expirada. Inicia sesión de nuevo.');
          setTimeout(() => (window.location.href = './login.html'), 1200);
        } else {
          mostrarMensajeError(data.error || 'No se pudo eliminar');
          btnElement.disabled = false;
          btnElement.innerHTML = original;
        }
      } catch (err) {
        mostrarMensajeError('Error al conectar con el servidor');
        btnElement.disabled = false;
      }
    }

    document.getElementById('btn-logout').addEventListener('click', async () => {
      try {
        const resp = await fetch('./api/logout.php');
        await resp.json();
      } catch {}
      window.location.href = './login.html';
    });

    // Exponer funciones globalmente para compatibilidad con onclick inline
    window.cambiarEstado = cambiarEstado;
    window.eliminarReserva = eliminarReserva;

    cargarReservas();
  </script>
</body>
</html>
