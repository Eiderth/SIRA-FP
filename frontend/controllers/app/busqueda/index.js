import Utils from "../../../core/utils.js"

export default class Busqueda_controller extends Utils {
    #html;

    #badge_total_resultados;
    #tbody;
    #form_busqueda;
    #btn_buscar;
    #btn_limpiar_filtros;
    #contenedor_tabla_busqueda;
    #observer = null;

    #pagina_actual = 1;
    #total_paginas = 1;
    #cargando = false;
    #filtros_actuales = {};

    constructor() {
        super();
        this.#html = this._traer_html('./frontend/views/app/busqueda/index.html');
    }

    async init() {
        const html = await this.#html;
        await this._inyectar_html(document.getElementById('root-app'), html);

        this.#tbody = document.getElementById('tabla-inscritos-body');
        this.#badge_total_resultados = document.getElementById('badge-total-resultados');
        this.#form_busqueda = document.getElementById('form-busqueda');
        this.#btn_buscar = document.getElementById('btn-buscar');
        this.#btn_limpiar_filtros = document.getElementById('btn-limpiar-filtros');
        this.#contenedor_tabla_busqueda = document.getElementById('contenedor-tabla-busqueda');

        await this.#insertar_parametros();
        await this.#ejecutar_busqueda(false);

        this.#btn_buscar.addEventListener('click', (e) => {
            e.preventDefault();
            this.#filtrar_busqueda();
        });

        this.#btn_limpiar_filtros.addEventListener('click', () => {
            this.#resetear_filtros();
        });

        this.#configurar_observer();
    }

    #configurar_observer() {
        const centinela = document.getElementById('fila-centinela');
        if (!centinela) return;

        if (this.#observer) {
            this.#observer.disconnect();
        }

        const opciones = {
            root: this.#contenedor_tabla_busqueda,
            rootMargin: '0px 0px 150px 0px',
            threshold: 0.1
        };

        this.#observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !this.#cargando && this.#pagina_actual < this.#total_paginas) {
                    this.#pagina_actual++;
                    this.#ejecutar_busqueda(true);
                }
            });
        }, opciones);

        this.#observer.observe(centinela);
    }

    async #insertar_parametros() {
        const { periodos = [], grados = [], secciones = [] } = await this._traer_datos('./api.php?controller=busqueda_controller&action=traer_parametros');

        this._llenar_select(document.getElementById('select-periodo'), [{ nombre: 'Todos los períodos', id: '' }, ...periodos], 'Todos los períodos');
        this._llenar_select(document.getElementById('select-grado'), [{ nombre: 'Todos los grados', id: '' }, ...grados], 'Todos los grados');
        this._llenar_select(document.getElementById('select-seccion'), [{ nombre: 'Todas las secciones', id: '' }, ...secciones], 'Todas las secciones');
    }

    async #resetear_filtros() {
        this.#btn_limpiar_filtros.disabled = true;
        this.#form_busqueda.reset();
        this.#filtros_actuales = {};
        this.#pagina_actual = 1;
        this.#contenedor_tabla_busqueda.scrollTop = 0;

        await this.#insertar_parametros();
        await this.#ejecutar_busqueda(false);

        this.#btn_limpiar_filtros.disabled = false;
        this.#configurar_observer();
    }

    async #filtrar_busqueda() {
        this.#btn_buscar.disabled = true;

        const formData = Object.fromEntries(new FormData(this.#form_busqueda));
        const filtros = {};

        for (let clave in formData) {
            const valor = formData[clave].trim();
            filtros[clave] = valor === "" ? null : valor;
        }

        this.#filtros_actuales = filtros;
        this.#pagina_actual = 1;
        this.#contenedor_tabla_busqueda.scrollTop = 0;

        await this.#ejecutar_busqueda(false);

        setTimeout(() => {
            this.#btn_buscar.disabled = false;
        }, 300);

        this.#configurar_observer();
    }

    async #ejecutar_busqueda(concatenar = false) {
        this.#cargando = true;

        if (!concatenar) {
            this.#pagina_actual = 1;
            this.#tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-secondary">
                        <div class="spinner-border spinner-border-sm me-2 text-primary" role="status"></div>
                        Buscando estudiantes...
                    </td>
                </tr>`;
        }

        const data = {
            ...this.#filtros_actuales,
            pagina: this.#pagina_actual
        };

        const {estudiantes, paginacion} = await this._enviar_datos('./api.php?controller=busqueda_controller&action=buscar_coincidencias', data);

        this.#cargando = false;

        if (!estudiantes || Object.keys(paginacion).length === 0) {
            if (!concatenar) {
                this.#tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger">Error al consultar registros</td></tr>`;
            }
            return;
        }

        this.#total_paginas = paginacion.total_paginas;

        if (estudiantes.length === 0 && !concatenar) {
            this.#tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="bi bi-search fs-4 d-block mb-1 opacity-50"></i>
                        <span class="small">No se encontraron registros con los filtros seleccionados.</span>
                    </td>
                </tr>`;
            return;
        }

        this.#badge_total_resultados.textContent = `${paginacion.total} Registros`;

        const filas_HTML = estudiantes.map(e => {
            const cedula = e.est_cedula_identidad ? `${e.est_nacionalidad || 'V'}-${e.est_cedula_identidad}` : (e.est_cedula_escolar || 'N/P');
            const estudiante_nombre = `${e.est_nombre_1} ${e.est_nombre_2?.charAt(0) ?? ''} ${e.est_apellido_1} ${e.est_apellido_2?.charAt(0)?? ''}`.trim();
            const rep_nombre = `${e.rep_nombre_1 || ''} ${e.rep_apellido_1 || ''}`.trim();
            const estado_clase = e.est_estado === 'Activo' ? 'text-primary' : 'text-secondary';

            return `
                <tr>
                    <td class="ps-3 py-2.5 fw-semibold text-secondary align-middle">${cedula}</td>
                    <td class="py-2.5 fw-bold text-dark align-middle">${estudiante_nombre}</td>            
                    <td class="py-2.5 align-middle">
                        <span class="d-block text-dark">${rep_nombre}</span>
                        <small class="text-muted">${e.rep_telefono_movil ?? ''}</small>
                    </td>
                    <td class="py-2.5 text-center align-middle">
                        <span class="${estado_clase} fw-semibold">${e.est_estado}</span>
                    </td>
                    <td class="pe-3 py-2.5 text-end align-middle">
                        <button type="button" data-id="${e.estudiante_id}" class="btn-buscar-data-estudiante btn btn-primary btn-sm text-nowrap py-0 px-2 fw-semibold">ver datos</button>
                    </td>
                </tr>`;
        }).join('');

        const centinela_HTML = `
            <tr id="fila-centinela">
                <td colspan="5" class="text-center py-3 text-muted border-0">
                    <small>${this.#pagina_actual < this.#total_paginas ? 'Cargando más registros...' : 'No hay más registros'}</small>
                </td>
            </tr>`;

        if (concatenar) {
            const centinelaEl = document.getElementById('fila-centinela');
            if (centinelaEl) centinelaEl.remove();

            this.#tbody.insertAdjacentHTML('beforeend', filas_HTML + centinela_HTML);
        } else {
            this.#tbody.innerHTML = filas_HTML + centinela_HTML;
        }

        this.#tbody.querySelectorAll('.btn-buscar-data-estudiante').forEach(btn => {
            btn.onclick = async (ev) => {
                const id = ev.currentTarget.getAttribute('data-id');
                if (!id) return;

                const resp = await this._enviar_datos('./api.php?controller=busqueda_controller&action=obtener_datos_estudiante', { 'estudiante_id': id });

                if (resp) {
                    this.#mostrar_expediente(resp);
                    return;
                }

                this._notificacion('algo ha salido mal');
            };
        });

        if (this.#pagina_actual < this.#total_paginas) {
            this.#configurar_observer();
        }
    }

    #mostrar_expediente(data) {
        const modalEl = document.getElementById('modalExpedienteEstudiante');
        const tabRepSecundario = document.getElementById('item-tab-rep-secundario');

        const val = (valor, fallback = 'No registra') => (valor !== null && valor !== undefined && valor !== '') ? valor : fallback;
        const mapeo = {
            'estudiante_nombre_completo': `${data.estudiante.persona.nombre_1} ${data.estudiante.persona.nombre_2 || ''} ${data.estudiante.persona.apellido_1} ${data.estudiante.persona.apellido_2 || ''}`.trim(),
            'estudiante_cedula_completa': `${data.estudiante.persona.nacionalidad}-${data.estudiante.persona.cedula_identidad}`,
            'estudiante_cedula_escolar': val(data.estudiante.persona_estudiante.cedula_escolar),
            'estudiante_estado': val(data.estudiante.persona_estudiante.estado),
            'persona.nombre_1': val(data.estudiante.persona.nombre_1),
            'persona.nombre_2': val(data.estudiante.persona.nombre_2, 'N/A'),
            'persona.apellido_1': val(data.estudiante.persona.apellido_1),
            'persona.apellido_2': val(data.estudiante.persona.apellido_2, 'N/A'),
            'persona.fecha_nacimiento': val(data.estudiante.persona.fecha_nacimiento),
            'persona.sexo': data.estudiante.persona.sexo === 'M' ? 'Masculino' : 'Femenino',
            'estudiante.lateralidad': val(data.estudiante.persona_estudiante.lateralidad),
            'estudiante.numero_hijo': val(data.estudiante.persona_estudiante.numero_hijo),
            'antropometrico.estatura': data.estudiante.antropometrico.estatura ? `${data.estudiante.antropometrico.estatura} cm` : 'No registra',
            'antropometrico.peso': data.estudiante.antropometrico.peso ? `${data.estudiante.antropometrico.peso} kg` : 'No registra',
            'antropometrico.talla_camisa': val(data.estudiante.antropometrico.talla_camisa),
            'antropometrico.talla_pantalon': val(data.estudiante.antropometrico.talla_pantalon),
            'antropometrico.talla_zapato': val(data.estudiante.antropometrico.talla_zapato),
            'salud.reacciones_alergicas': val(data.estudiante.salud.reacciones_alergicas),
            'salud.cuales_alergias': val(data.estudiante.salud.cuales_alergias),
            'salud.enfermedades_padecidas': val(data.estudiante.salud.enfermedades_padecidas),
            'salud.atencion_especial': val(data.estudiante.salud.atencion_especial),
            'salud.atendido_por_especialista': val(data.estudiante.salud.atendido_por_especialista),
            'salud.nombre_especialista': val(data.estudiante.salud.nombre_especialista),
            'extra_curricular.realiza_deportes': val(data.estudiante.extra_curricular.realiza_deportes),
            'extra_curricular.cuales_deportes': val(data.estudiante.extra_curricular.cuales_deportes),
            'extra_curricular.posee_canaima': val(data.estudiante.extra_curricular.posee_canaima),
            'extra_curricular.estado_canaima': val(data.estudiante.extra_curricular.estado_canaima),
            'extra_curricular.serial_canaima': val(data.estudiante.extra_curricular.serial_canaima),
            'extra_curricular.posee_cargador': val(data.estudiante.extra_curricular.posee_cargador),
            'extra_curricular.estado_cargador': val(data.estudiante.extra_curricular.estado_cargador),
            'rep_nombre_completo': `${data.representante_principal.persona.nombre_1} ${data.representante_principal.persona.nombre_2 || ''} ${data.representante_principal.persona.apellido_1} ${data.representante_principal.persona.apellido_2 || ''}`.trim(),
            'rep_cedula_completa': `${data.representante_principal.persona.nacionalidad}-${data.representante_principal.persona.cedula_identidad}`,
            'representante_principal.persona_representante.parentesco': val(data.representante_principal.persona_representante.parentesco),
            'representante_principal.persona_representante.telefono_movil': val(data.representante_principal.persona_representante.telefono_movil),
            'representante_principal.persona_representante.telefono_habitacion': val(data.representante_principal.persona_representante.telefono_habitacion),
            'representante_principal.persona_representante.correo_electronico': val(data.representante_principal.persona_representante.correo_electronico),
            'representante_principal.persona_representante.profesion': val(data.representante_principal.persona_representante.profesion),
            'representante_principal.persona_representante.estado_civil': val(data.representante_principal.persona_representante.estado_civil),
            'representante_principal.persona_representante.empresa_trabajo': val(data.representante_principal.persona_representante.empresa_trabajo)
        };

        if (data.representante_secundario && data.representante_secundario.persona) {
            tabRepSecundario.classList.remove('d-none');
            const repSec = data.representante_secundario;
            Object.assign(mapeo, {
                'rep_secundario_nombre_completo': `${repSec.persona.nombre_1} ${repSec.persona.nombre_2 || ''} ${repSec.persona.apellido_1} ${repSec.persona.apellido_2 || ''}`.trim(),
                'rep_secundario_cedula_completa': `${repSec.persona.nacionalidad}-${repSec.persona.cedula_identidad}`,
                'representante_secundario.persona_representante.parentesco': val(repSec.persona_representante.parentesco),
                'representante_secundario.persona_representante.telefono_movil': val(repSec.persona_representante.telefono_movil),
                'representante_secundario.persona_representante.telefono_habitacion': val(repSec.persona_representante.telefono_habitacion),
                'representante_secundario.persona_representante.correo_electronico': val(repSec.persona_representante.correo_electronico),
                'representante_secundario.persona_representante.profesion': val(repSec.persona_representante.profesion),
                'representante_secundario.persona_representante.estado_civil': val(repSec.persona_representante.estado_civil),
                'representante_secundario.persona_representante.empresa_trabajo': val(repSec.persona_representante.empresa_trabajo)
            });
        } else {
            tabRepSecundario.classList.add('d-none'); 
        }

        Object.keys(mapeo).forEach(key => {
            const elemento = modalEl.querySelector(`[data-field="${key}"]`);
            if (elemento) {
                elemento.textContent = mapeo[key];
            }
        });

        const primeraTab = new bootstrap.Tab(document.getElementById('tab-personales'));
        primeraTab.show();

        const e = data.estudiante.persona;
        document.getElementById('expediente-avatar-iniciales').textContent = `${e.nombre_1.charAt(0)}${e.apellido_1.charAt(0)}`.toUpperCase();

        const modalInstance = new bootstrap.Modal(modalEl);
        modalInstance.show();
    }
}