/**
 * Aplicación principal de JavaScript para el sistema de turnos
 */

// Clase para manejar la gestión de turnos
class TurnoApp {
    constructor() {
        // Detectar la ruta base de la aplicación automáticamente
        // Si estamos en /sistemaDeTurnos/public/, la ruta base será /sistemaDeTurnos/public
        // Si estamos en la raíz, será /
        let rutaBase = window.location.pathname;
        
        // Si la ruta incluye '/public/', usar hasta '/public'
        if (rutaBase.includes('/public')) {
            rutaBase = rutaBase.substring(0, rutaBase.indexOf('/public') + 7);
        } else {
            // Si no, usar el directorio del pathname actual
            rutaBase = rutaBase.replace(/\/[^\/]*$/, '');
        }
        
        // Asegurar que termine en /
        if (!rutaBase.endsWith('/') && rutaBase !== '') {
            rutaBase += '/';
        }
        
        // Si está vacío, usar / como base
        if (rutaBase === '' || rutaBase === '/') {
            this.apiUrl = '/api/turnos';
        } else {
            this.apiUrl = rutaBase + 'api/turnos';
        }
        
        this.inicializar();
    }

    inicializar() {
        // Establecer fecha mínima a hoy
        const fechaInput = document.getElementById('fecha');
        if (fechaInput) {
            fechaInput.min = new Date().toISOString().split('T')[0];
        }

        // Cargar turnos al cargar la página
        this.cargarTurnos();

        // Configurar evento del formulario
        const formulario = document.getElementById('formulario-turno');
        if (formulario) {
            formulario.addEventListener('submit', (e) => this.manejarEnvioFormulario(e));
        }
    }

    manejarEnvioFormulario(e) {
        e.preventDefault();
        
        const datosFormulario = new FormData(e.target);
        const datos = {};
        datosFormulario.forEach((valor, clave) => {
            if (valor) datos[clave] = valor;
        });

        const turnoId = document.getElementById('turno-id').value;
        
        if (turnoId) {
            datos.id = turnoId;
            this.actualizarTurno(datos);
        } else {
            this.crearTurno(datos);
        }
    }

    crearTurno(datos) {
        datos.accion = 'crear';
        this.hacerPeticion('POST', this.apiUrl, datos)
            .then(resultado => {
                if (resultado.exito) {
                    Utilidades.mostrarMensaje('Turno creado exitosamente', 'exito');
                    this.limpiarFormulario();
                    this.cargarTurnos();
                } else {
                    Utilidades.mostrarMensaje(resultado.mensaje || 'Error al crear el turno', 'error');
                }
            })
            .catch(error => {
                Utilidades.mostrarMensaje('Error de conexión', 'error');
                console.error('Error:', error);
            });
    }

    actualizarTurno(datos) {
        datos.accion = 'actualizar';
        this.hacerPeticion('POST', this.apiUrl, datos)
            .then(resultado => {
                if (resultado.exito) {
                    Utilidades.mostrarMensaje('Turno actualizado exitosamente', 'exito');
                    this.limpiarFormulario();
                    this.cargarTurnos();
                } else {
                    Utilidades.mostrarMensaje(resultado.mensaje || 'Error al actualizar el turno', 'error');
                }
            })
            .catch(error => {
                Utilidades.mostrarMensaje('Error de conexión', 'error');
                console.error('Error:', error);
            });
    }

    cargarTurnos() {
        const filtros = this.obtenerFiltros();
        const url = this.construirUrlConFiltros(filtros);

        this.hacerPeticion('GET', url)
            .then(resultado => {
                if (resultado.exito) {
                    this.mostrarTurnos(resultado.datos);
                } else {
                    Utilidades.mostrarMensaje('Error al cargar los turnos', 'error');
                }
            })
            .catch(error => {
                Utilidades.mostrarMensaje('Error de conexión', 'error');
                console.error('Error:', error);
            });
    }

    mostrarTurnos(turnos) {
        const cuerpoTabla = document.getElementById('cuerpo-tabla');
        
        if (!cuerpoTabla) return;
        
        if (turnos.length === 0) {
            cuerpoTabla.innerHTML = '<tr><td colspan="9" style="text-align: center;">No hay turnos registrados</td></tr>';
            return;
        }

        cuerpoTabla.innerHTML = turnos.map(turno => this.generarFilaTabla(turno)).join('');
    }

    generarFilaTabla(turno) {
        return `
            <tr>
                <td>${turno.id}</td>
                <td>${turno.nombre}</td>
                <td>${turno.email}</td>
                <td>${turno.telefono}</td>
                <td>${turno.fecha}</td>
                <td>${turno.hora}</td>
                <td>${turno.servicio}</td>
                <td>${this.generarBadgeEstado(turno.estado)}</td>
                <td>${this.generarBotonesAcciones(turno.id)}</td>
            </tr>
        `;
    }

    generarBadgeEstado(estado) {
        const textos = {
            'pendiente': 'Pendiente',
            'confirmado': 'Confirmado',
            'cancelado': 'Cancelado',
            'completado': 'Completado'
        };
        const texto = textos[estado] || estado;
        return `<span class="estado estado-${estado}">${texto}</span>`;
    }

    generarBotonesAcciones(id) {
        return `
            <div class="acciones">
                <button class="boton boton-primario boton-pequeno" onclick="turnoApp.editarTurno(${id})">Editar</button>
                <button class="boton boton-peligro boton-pequeno" onclick="turnoApp.eliminarTurno(${id})">Eliminar</button>
            </div>
        `;
    }

    editarTurno(id) {
        this.hacerPeticion('GET', `${this.apiUrl}?id=${id}`)
            .then(resultado => {
                if (resultado.exito) {
                    this.cargarTurnoEnFormulario(resultado.datos);
                    document.querySelector('.contenedor-formulario').scrollIntoView({ behavior: 'smooth' });
                } else {
                    Utilidades.mostrarMensaje('Error al cargar el turno', 'error');
                }
            })
            .catch(error => {
                Utilidades.mostrarMensaje('Error de conexión', 'error');
                console.error('Error:', error);
            });
    }

    cargarTurnoEnFormulario(turno) {
        // Debug solo en desarrollo (eliminar en producción)
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            console.log('Datos del turno recibidos:', turno);
        }
        
        document.getElementById('turno-id').value = turno.id || '';
        document.getElementById('nombre').value = turno.nombre || '';
        document.getElementById('email').value = turno.email || '';
        document.getElementById('telefono').value = turno.telefono || '';
        
        // Manejar fecha - asegurar formato YYYY-MM-DD
        const fechaCampo = document.getElementById('fecha');
        if (turno.fecha) {
            // Si viene en formato completo (con hora), extraer solo la fecha
            const fecha = turno.fecha.split(' ')[0];
            fechaCampo.value = fecha;
        } else {
            fechaCampo.value = '';
        }
        
        // Manejar hora - asegurar formato HH:mm
        const horaCampo = document.getElementById('hora');
        if (turno.hora) {
            // Si viene en formato completo, extraer solo la hora
            const hora = turno.hora.split(' ')[0].substring(0, 5); // Toma HH:mm
            horaCampo.value = hora;
        } else {
            horaCampo.value = '';
        }
        
        document.getElementById('servicio').value = turno.servicio || '';
        document.getElementById('estado').value = turno.estado || 'pendiente';
        document.getElementById('notas').value = turno.notas || '';
        document.getElementById('boton-cancelar').style.display = 'inline-block';
        document.getElementById('titulo-formulario').textContent = 'Editar Turno';
    }

    eliminarTurno(id) {
        if (!confirm('¿Está seguro de que desea eliminar este turno?')) {
            return;
        }

        // Asegurar que el ID sea numérico
        const idNumerico = parseInt(id, 10);
        if (isNaN(idNumerico)) {
            Utilidades.mostrarMensaje('ID de turno inválido', 'error');
            console.error('ID inválido recibido:', id);
            return;
        }

        const datosEnvio = { accion: 'eliminar', id: idNumerico };
        
        // Debug solo en desarrollo
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            console.log('Enviando petición de eliminación:', datosEnvio);
        }

        this.hacerPeticion('POST', this.apiUrl, datosEnvio)
            .then(resultado => {
                // Debug solo en desarrollo
                if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                    console.log('Respuesta del servidor:', resultado);
                }
                if (resultado.exito) {
                    Utilidades.mostrarMensaje('Turno eliminado exitosamente', 'exito');
                    this.cargarTurnos();
                } else {
                    Utilidades.mostrarMensaje(resultado.mensaje || 'Error al eliminar el turno', 'error');
                    if (resultado.debug) {
                        console.error('Debug del servidor:', resultado.debug);
                    }
                }
            })
            .catch(error => {
                Utilidades.mostrarMensaje('Error de conexión', 'error');
                console.error('Error completo:', error);
            });
    }

    limpiarFormulario() {
        const formulario = document.getElementById('formulario-turno');
        if (formulario) {
            formulario.reset();
            document.getElementById('turno-id').value = '';
            document.getElementById('boton-cancelar').style.display = 'none';
            document.getElementById('titulo-formulario').textContent = 'Nuevo Turno';
        }
    }

    cancelarEdicion() {
        this.limpiarFormulario();
    }

    limpiarFiltros() {
        document.getElementById('filtro-fecha').value = '';
        document.getElementById('filtro-estado').value = '';
        document.getElementById('filtro-email').value = '';
        this.cargarTurnos();
    }

    obtenerFiltros() {
        return {
            fecha: document.getElementById('filtro-fecha')?.value || '',
            estado: document.getElementById('filtro-estado')?.value || '',
            email: document.getElementById('filtro-email')?.value || ''
        };
    }

    construirUrlConFiltros(filtros) {
        let url = this.apiUrl;
        const parametros = [];

        if (filtros.fecha) parametros.push(`fecha=${filtros.fecha}`);
        if (filtros.estado) parametros.push(`estado=${filtros.estado}`);
        if (filtros.email) parametros.push(`email=${filtros.email}`);

        if (parametros.length > 0) {
            url += '?' + parametros.join('&');
        }

        return url;
    }

    hacerPeticion(metodo, url, datos = null) {
        const opciones = {
            method: metodo,
            headers: {
                'Content-Type': 'application/json',
            }
        };

        if (datos && metodo === 'POST') {
            opciones.body = JSON.stringify(datos);
        }

        return fetch(url, opciones)
            .then(respuesta => respuesta.json());
    }
}

// Clase de utilidades
class Utilidades {
    static mostrarMensaje(mensaje, tipo) {
        const mensajeDiv = document.getElementById('mensaje');
        if (!mensajeDiv) return;

        mensajeDiv.className = `alerta alerta-${tipo}`;
        mensajeDiv.textContent = mensaje;
        mensajeDiv.style.display = 'block';

        setTimeout(() => {
            mensajeDiv.style.display = 'none';
        }, 5000);
    }
}

// Funciones globales para compatibilidad con onclick
function cancelarEdicion() {
    turnoApp.cancelarEdicion();
}

function limpiarFiltros() {
    turnoApp.limpiarFiltros();
}

function cargarTurnos() {
    turnoApp.cargarTurnos();
}

// Inicializar la aplicación cuando el DOM esté listo
let turnoApp;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        turnoApp = new TurnoApp();
    });
} else {
    turnoApp = new TurnoApp();
}
