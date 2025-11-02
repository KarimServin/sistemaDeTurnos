/**
 * Aplicación principal de JavaScript para el sistema de turnos
 */

// Utilidad para escapar HTML y prevenir XSS
const EscapadorHtml = {
    /**
     * Escapa texto para prevenir XSS
     */
    escapar(texto) {
        if (texto === null || texto === undefined) {
            return '';
        }
        const div = document.createElement('div');
        div.textContent = String(texto);
        return div.innerHTML;
    },
    
    /**
     * Valida y escapa un ID numérico
     */
    validarId(id) {
        const idNumerico = parseInt(id, 10);
        if (isNaN(idNumerico) || idNumerico <= 0) {
            console.error('ID inválido:', id);
            return null;
        }
        return idNumerico;
    }
};

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
        // Escapar todos los datos del usuario antes de insertarlos en el HTML para prevenir XSS
        return `
            <tr>
                <td>${EscapadorHtml.escapar(turno.id)}</td>
                <td>${EscapadorHtml.escapar(turno.nombre)}</td>
                <td>${EscapadorHtml.escapar(turno.email)}</td>
                <td>${EscapadorHtml.escapar(turno.telefono)}</td>
                <td>${EscapadorHtml.escapar(turno.fecha)}</td>
                <td>${EscapadorHtml.escapar(turno.hora)}</td>
                <td>${EscapadorHtml.escapar(turno.servicio)}</td>
                <td>${this.generarBadgeEstado(turno.estado)}</td>
                <td>${this.generarBotonesAcciones(turno.id)}</td>
            </tr>
        `;
    }

    generarBadgeEstado(estado) {
        // Validar que el estado sea uno permitido para prevenir XSS en atributos
        const estadosPermitidos = ['pendiente', 'confirmado', 'cancelado', 'completado'];
        const estadoLimpio = estadosPermitidos.includes(estado) ? estado : 'pendiente';
        
        const textos = {
            'pendiente': 'Pendiente',
            'confirmado': 'Confirmado',
            'cancelado': 'Cancelado',
            'completado': 'Completado'
        };
        
        // Escapar el texto para prevenir XSS
        const texto = textos[estadoLimpio] || EscapadorHtml.escapar(estadoLimpio);
        // estadoLimpio ya está validado contra lista permitida, seguro para usar en clase CSS
        return `<span class="estado estado-${estadoLimpio}">${texto}</span>`;
    }

    generarBotonesAcciones(id) {
        // Validar que el ID sea numérico para prevenir inyección de código
        const idNumerico = EscapadorHtml.validarId(id);
        if (idNumerico === null) {
            return '';
        }
        
        return `
            <div class="acciones">
                <button class="boton boton-primario boton-pequeno" onclick="turnoApp.editarTurno(${idNumerico})">Editar</button>
                <button class="boton boton-peligro boton-pequeno" onclick="turnoApp.eliminarTurno(${idNumerico})">Eliminar</button>
            </div>
        `;
    }

    editarTurno(id) {
        // Validar ID antes de hacer la petición
        const idNumerico = EscapadorHtml.validarId(id);
        if (idNumerico === null) {
            Utilidades.mostrarMensaje('ID de turno inválido', 'error');
            return;
        }
        
        // Escapar el ID en la URL para prevenir inyección
        const idEscapado = encodeURIComponent(idNumerico);
        this.hacerPeticion('GET', `${this.apiUrl}?id=${idEscapado}`)
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
        
        // Los campos de formulario escapan automáticamente, pero por seguridad también lo hacemos aquí
        // Usar textContent/value es seguro porque el navegador escapa automáticamente
        const elementos = {
            'turno-id': turno.id || '',
            'nombre': turno.nombre || '',
            'email': turno.email || '',
            'telefono': turno.telefono || ''
        };
        
        // Asignar valores de forma segura (el navegador escapa automáticamente en .value)
        Object.keys(elementos).forEach(id => {
            const elemento = document.getElementById(id);
            if (elemento) {
                elemento.value = elementos[id];
            }
        });
        
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

        // Validar ID usando el validador centralizado
        const idNumerico = EscapadorHtml.validarId(id);
        if (idNumerico === null) {
            Utilidades.mostrarMensaje('ID de turno inválido', 'error');
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

        // Escapar todos los parámetros de URL para prevenir inyección
        if (filtros.fecha) {
            parametros.push(`fecha=${encodeURIComponent(filtros.fecha)}`);
        }
        if (filtros.estado) {
            // Validar que el estado sea uno permitido
            const estadosPermitidos = ['pendiente', 'confirmado', 'cancelado', 'completado'];
            if (estadosPermitidos.includes(filtros.estado)) {
                parametros.push(`estado=${encodeURIComponent(filtros.estado)}`);
            }
        }
        if (filtros.email) {
            parametros.push(`email=${encodeURIComponent(filtros.email)}`);
        }

        if (parametros.length > 0) {
            url += '?' + parametros.join('&');
        }

        return url;
    }

    hacerPeticion(metodo, url, datos = null) {
        // Validar método HTTP
        const metodosPermitidos = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
        const metodoValido = metodosPermitidos.includes(metodo.toUpperCase()) ? metodo.toUpperCase() : 'GET';
        
        const opciones = {
            method: metodoValido,
            headers: {
                'Content-Type': 'application/json',
            }
        };

        if (datos && (metodoValido === 'POST' || metodoValido === 'PUT' || metodoValido === 'PATCH')) {
            opciones.body = JSON.stringify(datos);
        }

        return fetch(url, opciones)
            .then(respuesta => {
                // Validar que la respuesta sea válida
                if (!respuesta.ok) {
                    throw new Error(`HTTP error! status: ${respuesta.status}`);
                }
                return respuesta.json();
            })
            .catch(error => {
                console.error('Error en petición:', error);
                throw error;
            });
    }
}

// Clase de utilidades
class Utilidades {
    static mostrarMensaje(mensaje, tipo) {
        const mensajeDiv = document.getElementById('mensaje');
        if (!mensajeDiv) return;

        // Validar tipo de mensaje
        const tiposPermitidos = ['info', 'exito', 'error', 'advertencia'];
        const tipoLimpio = tiposPermitidos.includes(tipo) ? tipo : 'info';
        
        mensajeDiv.className = `alerta alerta-${tipoLimpio}`;
        // textContent escapa automáticamente, así que es seguro
        mensajeDiv.textContent = mensaje || '';
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
