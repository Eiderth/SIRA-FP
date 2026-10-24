import Utils from "../../../core/utils.js";

export default class Reportes_controlller extends Utils {
    #html;
    #badgeTotal;
    #tbody;
    #formulario_filtrar_reportes;
    #btn_filtrar_reportes;
    #btn_limpiar_filtros;
    #contenedor_tabla_reportes;
    #observer = null; 

    #pagina_actual = 1;
    #total_paginas = 1;
    #cargando = false;
    #parametros_formulario = {}; 

    constructor() {
        super();
        this.#html = this._traer_html('./frontend/views/app/reportes/index.html');
    }   

    async init() {
        const html = await this.#html;
        
        await this._inyectar_html(document.getElementById('root-app'), html);
        
        this.#tbody = document.getElementById('tabla-inscritos-body');
        this.#badgeTotal = document.getElementById('total-inscritos');
        this.#formulario_filtrar_reportes = document.getElementById('formulario-filtrar-reportes');
        this.#btn_filtrar_reportes = document.getElementById('btn-filtrar');
        this.#btn_limpiar_filtros = document.getElementById('btn-limpiar-filtros');
        this.#contenedor_tabla_reportes = document.getElementById('contenedor-tabla-reportes');

        await this.#cargar_parametros_iniciales();
        await this.#cargar_reportes(false); 

        this.#btn_filtrar_reportes.addEventListener('click', () => this.#filtrar_reportes());
        this.#btn_limpiar_filtros.addEventListener('click', () => this.#resetear_filtros());
        document.getElementById('btn-exportar-reporte').addEventListener('click', () => this.#exportar_reporte());

        this.#configurar_eventos_ubicacion();
    }

    #configurar_observer() {
        const centinela = document.getElementById('fila-centinela');
        if (!centinela) return;

        if (this.#observer) {
            this.#observer.disconnect();
        }

        const opciones = {
            root: this.#contenedor_tabla_reportes,
            rootMargin: '0px 0px 150px 0px',
            threshold: 0.1
        };

        this.#observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !this.#cargando && this.#pagina_actual < this.#total_paginas) {
                    this.#pagina_actual++;
                    this.#cargar_reportes(true);
                }
            });
        }, opciones);

        this.#observer.observe(centinela);
    }

    async #cargar_parametros_iniciales() {
        const respuesta = await this._traer_datos('./api.php?controller=reportes_controller&action=traer_parametros_filtros');

        if (!respuesta) return;

        const {
            periodos_academicos = [], 
            grados = [], 
            secciones = [],
            estados = [],
            municipios = [],
            parroquias = [],
            profesiones = []
        } = respuesta;

        this.#parametros_formulario = { estados, municipios, parroquias };

        this._llenar_select(document.getElementById('filtro-periodo'), [{nombre: 'Todos los períodos', id: ''}, ...periodos_academicos], 'Todos los períodos');
        this._llenar_select(document.getElementById('filtro-grado'), [{nombre: 'Todos los grados', id: ''}, ...grados], 'Todos los grados');
        this._llenar_select(document.getElementById('filtro-seccion'), [{nombre: 'Todas las secciones', id: ''}, ...secciones], 'Todas las secciones');

        if (estados.length > 0) {
            this._llenar_select(document.getElementById('filtro-estado'), [{nombre: 'Todos los estados', id: ''}, ...estados], 'Todos los estados');
        }
        if (municipios.length > 0) {
            this._llenar_select(document.getElementById('filtro-municipio'), [{nombre: 'Todos los municipios', id: ''}, ...municipios], 'Todos los municipios');
        }
        if (parroquias.length > 0) {
            this._llenar_select(document.getElementById('filtro-parroquia'), [{nombre: 'Todas las parroquias', id: ''}, ...parroquias], 'Todas las parroquias');
        }

        const selectProfesion = document.getElementById('filtro-profesion');
        if (selectProfesion && profesiones.length > 0) {
            this._llenar_select(selectProfesion, [{nombre: 'Todas las profesiones', id: ''}, ...profesiones], 'Todas las profesiones');
        }
    }

    #configurar_eventos_ubicacion() {
        const select_estado = document.getElementById('filtro-estado');
        const select_municipio = document.getElementById('filtro-municipio');
        const select_parroquia = document.getElementById('filtro-parroquia');

        if (!select_estado || !select_municipio || !select_parroquia) return;

        select_estado.addEventListener('change', (e) => {
            const estado_id = e.target.value;
            const municipios_filtrados = estado_id === '' 
                ? [{nombre: 'Todos los municipios', id: ''}, ...(this.#parametros_formulario.municipios || [])]
                : [{nombre: 'Todos los municipios', id: ''}, ...(this.#parametros_formulario.municipios || []).filter(m => m.estado_id == estado_id)];
            
            this._llenar_select(select_municipio, municipios_filtrados, '');
            this._llenar_select(select_parroquia, [{nombre: 'Todas las parroquias', id: ''}], '');
        });

        select_municipio.addEventListener('change', (e) => {
            const municipio_id = e.target.value;
            const parroquias_filtradas = municipio_id === ''
                ? [{nombre: 'Todas las parroquias', id: ''}, ...(this.#parametros_formulario.parroquias || [])]
                : [{nombre: 'Todas las parroquias', id: ''}, ...(this.#parametros_formulario.parroquias || []).filter(p => p.municipio_id == municipio_id)];
            
            this._llenar_select(select_parroquia, parroquias_filtradas, '');
        });
    }

    async #resetear_filtros() {
        this.#btn_limpiar_filtros.disabled = true;    
        this.#formulario_filtrar_reportes.reset();

        await this.#cargar_parametros_iniciales();
        this.#pagina_actual = 1;
        this.#contenedor_tabla_reportes.scrollTop = 0;
        await this.#cargar_reportes(false);

        this.#btn_limpiar_filtros.disabled = false;    
    }

    async #filtrar_reportes() {
        this.#btn_filtrar_reportes.disabled = true;

        this.#pagina_actual = 1;
        this.#contenedor_tabla_reportes.scrollTop = 0;

        await this.#cargar_reportes(false);

        setTimeout(() => {
            this.#btn_filtrar_reportes.disabled = false;
        }, 300);
    }

    async #cargar_reportes(concatenar = false) {
        this.#cargando = true;

        if (!concatenar) {
            this.#pagina_actual = 1;
            this.#tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-secondary">
                        <div class="spinner-border spinner-border-sm me-2 text-primary" role="status"></div>
                        Cargando inscritos...
                    </td>
                </tr>`;
        }

        const filtros_actuales = Object.fromEntries(new FormData(this.#formulario_filtrar_reportes));
        this._limpiar_objeto(filtros_actuales);

        const {inscripciones, paginacion} = await this._enviar_datos(
            './api.php?controller=reportes_controller&action=filtrar_reportes', 
            {
                ...filtros_actuales,
                pagina: this.#pagina_actual    
            }
        );

        this.#cargando = false;

        if (!inscripciones || Object.keys(paginacion) === 0) {
            if (!concatenar) {
                this.#tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger">Error al cargar listado</td></tr>`;
            }
            return;
        }

        this.#total_paginas = paginacion.total_paginas;
        this.#badgeTotal.textContent = `${paginacion.total_registros} registros`;

        if (inscripciones.length === 0 && !concatenar) {
            this.#tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-secondary">No hay registros con los filtros seleccionados</td></tr>`;
            return;
        }

        const filas_HTML = inscripciones.map((i, index) => {
            const doc = i.cedula_identidad ? `${i.nacionalidad || 'V'}-${i.cedula_identidad}` : (i.cedula_escolar || 'N/P');
            const nombre_completo = `${i.nombre_1} ${i.apellido_1}`.trim();
            const grado_seccion = `${i.grado} - "${i.seccion}"`;
            const estado_badge = i.estado === 'Activo' 
                ? 'bg-success-subtle text-success' 
                : 'bg-warning-subtle text-secondary';

            return `
                <tr data-id="${i.id_inscripcion}">
                    <td class="ps-2 py-2 align-middle font-monospace fw-semibold">${doc}</td>
                    <td class="py-2 align-middle text-dark fw-bold">${nombre_completo}</td>
                    <td class="py-2 align-middle"><span class="badge bg-light text-dark border">${i.periodo || 'N/P'}</span></td>
                    <td class="py-2 align-middle fw-semibold">${grado_seccion}</td>
                    <td class="py-2 align-middle text-center"><span class="badge ${estado_badge} rounded-pill px-2 py-1">${i.estado || 'Inscrito'}</span></td>
                    <td class="pe-2 py-2 align-middle text-end">
                        <button type="button" data-id="${i.id_inscripcion}" title="Generar Planilla PDF" class="btn-generar-pdf btn btn-outline-danger btn-sm py-0 px-2 fw-semibold">
                            <i class="bi bi-file-earmark-pdf-fill me-1"></i>PDF
                        </button>
                    </td>
                </tr>`;
        }).join('');

        const hay_mas = this.#pagina_actual < this.#total_paginas;
        const centinela_HTML = `
            <tr id="fila-centinela">
                <td colspan="7" class="text-center py-3 text-muted border-0">
                    <small>${hay_mas ? 'Cargando más registros...' : 'No hay más registros'}</small>
                </td>
            </tr>`;

        if (concatenar) {
            const centinelaEl = document.getElementById('fila-centinela');
            if (centinelaEl) centinelaEl.remove();

            this.#tbody.insertAdjacentHTML('beforeend', filas_HTML + centinela_HTML);
        } else {
            this.#tbody.innerHTML = filas_HTML + centinela_HTML;
        }

        this.#inicializar_botones_pdf();

        if (hay_mas) {
            this.#configurar_observer();
        }
    }

    #inicializar_botones_pdf() {
        this.#tbody.querySelectorAll('.btn-generar-pdf').forEach(btn => {
            btn.onclick = (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                this.#generar_pdf(id);
            };
        });
    }

    async #generar_pdf(id) {
        const respuesta = await this._traer_datos(`./api.php?controller=reportes_controller&action=detalles_planilla&id=${id}`);
        
        if (!respuesta || !respuesta.datos) {
            this._notificacion('Error al traer datos', 'Error');
            return;
        } 

        const datos = respuesta.datos;
        const e = datos.inscripcion; // Estudiante + Académico + Ubicación
        const rep = datos.representante_principal;
        const repSec = datos.representante_secundario || null;
        const salud = datos.salud || {};
        const ant = datos.antropometrico || {};
        const ext = datos.extra_curricular || {};

        const vista = await this._traer_html('./frontend/views/app/reportes/planilla.html');

        const ventana_impresion = window.open('', '_blank');
        ventana_impresion.document.write(vista);
        ventana_impresion.document.close();

        ventana_impresion.onload = () => {
            const d = ventana_impresion.document;

            // --- ACADÉMICO E INSCRIPCIÓN ---
            d.getElementById('pl-periodo').textContent = e.periodo || 'N/P';
            d.getElementById('pl-nivel').textContent = e.nivel_academico || 'N/P';
            d.getElementById('pl-grado').textContent = e.grado || 'N/P';
            d.getElementById('pl-seccion').textContent = e.seccion || 'N/P';
            d.getElementById('pl-ingreso').textContent = e.tipo_ingreso || 'N/P';
            d.getElementById('pl-colegio-procedencia').textContent = e.colegio_procedencia || 'N/A';

            // --- DATOS PERSONALES DEL ESTUDIANTE ---
            d.getElementById('pl-nacionalidad').textContent = e.nacionalidad || 'V';
            d.getElementById('pl-ci').textContent = e.cedula_identidad || 'No posee';
            d.getElementById('pl-escolar').textContent = e.cedula_escolar || 'N/P';
            d.getElementById('pl-nombres').textContent = `${e.nombre_1 || ''} ${e.nombre_2 || ''}`.trim();
            d.getElementById('pl-apellidos').textContent = `${e.apellido_1 || ''} ${e.apellido_2 || ''}`.trim();
            d.getElementById('pl-nacimiento').textContent = e.fecha_nacimiento || 'N/P';
            d.getElementById('pl-sexo').textContent = e.sexo || 'N/P';
            d.getElementById('pl-lateralidad').textContent = e.lateralidad || 'N/P';

            // --- UBICACIÓN DOMICILIARIA ---
            d.getElementById('pl-estado').textContent = e.estado_nombre || 'N/P';
            d.getElementById('pl-municipio').textContent = e.municipio_nombre || 'N/P';
            d.getElementById('pl-parroquia').textContent = e.parroquia_nombre || 'N/P';
            d.getElementById('pl-sector').textContent = e.sector || 'N/P';
            d.getElementById('pl-calle').textContent = e.calle || 'N/P';
            d.getElementById('pl-vivienda').textContent = `${e.tipo_vivienda || ''} - N° ${e.numero_vivienda || 'S/N'}`;

            // --- ANTROPOMETRÍA ---
            d.getElementById('pl-estatura').textContent = ant.estatura ? `${ant.estatura} m` : 'N/P';
            d.getElementById('pl-peso').textContent = ant.peso ? `${ant.peso} kg` : 'N/P';
            d.getElementById('pl-t-camisa').textContent = ant.talla_camisa || 'N/P';
            d.getElementById('pl-t-pantalon').textContent = ant.talla_pantalon || 'N/P';
            d.getElementById('pl-t-zapato').textContent = ant.talla_zapato || 'N/P';

            // --- SALUD ---
            d.getElementById('pl-alergias').textContent = salud.reacciones_alergicas === 'Si' ? `Sí: ${salud.cuales_alergias || ''}` : 'No';
            d.getElementById('pl-enfermedades').textContent = salud.enfermedades_padecidas || 'Ninguna';
            d.getElementById('pl-atencion').textContent = salud.atencion_especial === 'Si' ? `Sí (${salud.horario_tratamiento || 'Sin horario especificado'})` : 'No';

            // --- EXTRA CURRICULAR / CANAIMA ---
            d.getElementById('pl-deportes').textContent = ext.realiza_deportes === 'Si' ? `Sí: ${ext.cuales_deportes || ''}` : 'No';
            d.getElementById('pl-canaima').textContent = ext.posee_canaima === 'Si' ? `Sí - Serial: ${ext.serial_canaima || 'N/S'} (${ext.estado_canaima || 'Operativa'})` : 'No posee';

            // --- REPRESENTANTE PRINCIPAL ---
            if (rep) {
                d.getElementById('pl-rep-ci').textContent = `${rep.nacionalidad || 'V'}-${rep.cedula_identidad || ''}`;
                d.getElementById('pl-rep-nombre').textContent = `${rep.nombre_1 || ''} ${rep.apellido_1 || ''}`.trim();
                d.getElementById('pl-rep-parentesco').textContent = rep.parentesco || 'N/P';
                d.getElementById('pl-rep-telefono').textContent = rep.telefono_movil || 'N/P';
                d.getElementById('pl-rep-profesion').textContent = rep.profesion || 'N/P';
                d.getElementById('pl-rep-ingreso').textContent = rep.ingreso_mensual ? `$${rep.ingreso_mensual}` : '0';
            }

            // --- REPRESENTANTE SECUNDARIO (Si existe) ---
            if (repSec) {
                d.getElementById('p2-rep-ci').textContent = `${repSec.nacionalidad || 'V'}-${repSec.cedula_identidad || ''}`;
                d.getElementById('p2-rep-nombre').textContent = `${repSec.nombre_1 || ''} ${repSec.apellido_1 || ''}`.trim();
                d.getElementById('p2-rep-parentesco').textContent = repSec.parentesco || 'N/P';
                d.getElementById('p2-rep-telefono').textContent = repSec.telefono_movil || 'N/P';
                d.getElementById('p2-rep-profesion').textContent = repSec.profesion || 'N/P';
                d.getElementById('p2-rep-ingreso').textContent = repSec.ingreso_mensual ? `$${repSec.ingreso_mensual}` : '0';

                // Ubicación del representante secundario
                d.getElementById('p2-estado').textContent = repSec.estado_nombre || 'N/P';
                d.getElementById('p2-municipio').textContent = repSec.municipio_nombre || 'N/P';
                d.getElementById('p2-parroquia').textContent = repSec.parroquia_nombre || 'N/P';
                d.getElementById('p2-sector').textContent = repSec.sector || 'N/P';
                d.getElementById('p2-calle').textContent = repSec.calle || 'N/P';
                d.getElementById('p2-vivienda').textContent = `${repSec.tipo_vivienda || ''} - N° ${repSec.numero_vivienda || 'S/N'}`;
            }

            setTimeout(() => { ventana_impresion.print(); }, 400);
        };
    }

    #exportar_reporte() {
        const filtros_actuales = Object.fromEntries(new FormData(this.#formulario_filtrar_reportes));
        this._limpiar_objeto(filtros_actuales);
        const params = new URLSearchParams();
        
        for (const [clave, valor] of Object.entries(filtros_actuales)) {
            if (valor !== null && valor !== undefined && valor !== "") {
                params.append(clave, valor);
            }
        }

        const url = `./api.php?controller=reportes_controller&action=exportar_reportes&${params.toString()}`;        
        window.location.href = url;
    }
}