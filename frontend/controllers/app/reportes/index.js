import Utils from "../../../core/utils.js"


export default class Reportes_controlller extends Utils {
    #html;

    #badgeTotal;
    #tbody;
    #formulario_filtrar_reportes;
    #btn_filtrar_reportes;
    #btn_limpiar_filtros

    #contenedor_tabla_reportes;

    #pagina_actual = 1;
    #total_paginas = 1;
    #cargando = false;
    #filtros_actuales = {};

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

        this.#llenar_filtros();
        this.#cargar_reportes();
        this.#btn_filtrar_reportes.addEventListener('click', () => this.#filtrar_reportes());
        this.#btn_limpiar_filtros.addEventListener('click',() => this.#resetear_filtros());
        this.#contenedor_tabla_reportes.addEventListener('scroll', () => this.#paginacion());
        document.getElementById('btn-exportar-reporte').addEventListener('click', () => this.#exportar_reporte());
    }

    #paginacion() {
   
        const llego_al_final = (this.#contenedor_tabla_reportes.scrollTop + this.#contenedor_tabla_reportes.clientHeight) >= (this.#contenedor_tabla_reportes.scrollHeight - 20);

        if (llego_al_final && !this.#cargando && this.#pagina_actual < this.#total_paginas) {
            this.#pagina_actual++;
            this.#cargar_reportes(true);
        }
        
    }

    async #llenar_filtros(){

        const {periodos_academicos = null, grados = null, secciones = null} = await this._traer_datos('./api.php?controller=reportes_controller&action=traer_parametros_filtros');

        if(periodos_academicos && grados && secciones) {

            this._llenar_select(document.getElementById('filtro-periodo'), [{nombre: 'Todos los periodos', id: ''}, ...periodos_academicos ], 'Todos los periodos');
            this._llenar_select(document.getElementById('filtro-grado'), [{nombre: 'Todos los grados', id: ''}, ...grados ], 'Todos los grados');
            this._llenar_select(document.getElementById('filtro-seccion'), [{nombre: 'Todas las secciones', id: ''}, ...secciones], 'Todas las secciones');            
        
        }
    }

    async #resetear_filtros() {
    
        this.#btn_limpiar_filtros.disabled = true;    
        this.#formulario_filtrar_reportes.reset();
        
        this.#filtros_actuales = {};

        await this.#llenar_filtros();
        await this.#cargar_reportes();

        this.#btn_limpiar_filtros.disabled = false;    
    }

    async #filtrar_reportes() {

        this.#btn_filtrar_reportes.disabled = true;

        const formData = Object.fromEntries(new FormData(this.#formulario_filtrar_reportes));
        const filtros = {};

        for (let clave in formData) {
            const valor = formData[clave].trim();
            filtros[clave] = valor === "" ? null : valor;
        }

        this.#filtros_actuales = filtros;
        this.#pagina_actual = 1;

        this.#contenedor_tabla_reportes.scrollTop = 0;

        await this.#cargar_reportes(false);

        this.#btn_filtrar_reportes.disabled = false;

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


        const { inscripciones, paginacion } = await this._enviar_datos(
            './api.php?controller=reportes_controller&action=filtrar_reportes', 
            {
                ...this.#filtros_actuales,
                pagina: this.#pagina_actual   
            }
        );

        this.#cargando = false;

        if (!inscripciones || !paginacion) {
            if (!concatenar) {
                this.#tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger">Error al cargar listado</td></tr>`;
            }
            return;
        }

        this.#total_paginas = paginacion.total_paginas;
        this.#badgeTotal.textContent = `${paginacion.total_registros} Alumnos`;

        if (inscripciones.length === 0 && !concatenar) {
            this.#tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-secondary">No hay registros</td></tr>`;
            return;
        }

        const filas_HTML = inscripciones.map((i, index) => {
            const doc = i.cedula_identidad ? `${i.nacionalidad || 'V'}-${i.cedula_identidad}` : (i.cedula_escolar || 'N/P');
            const nombre_completo = `${i.nombre_1} ${i.apellido_1}`.trim();
            const grado_seccion = `${i.grado} - "${i.seccion}"`;
            const estado_badge = i.estado === 'Activo' 
                ? 'bg-success-subtle text-success' 
                : 'bg-warning-subtle text-secondary';

            const numero_fila = ((this.#pagina_actual - 1) * paginacion.limite) + (index + 1);

            return `
                <tr data-id="${i.id_inscripcion}">
                    <td class="ps-2 py-2 text-secondary fw-bold align-middle">${numero_fila}</td>
                    <td class="py-2 align-middle font-monospace fw-semibold">${doc}</td>
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

        if (concatenar) {
            this.#tbody.insertAdjacentHTML('beforeend', filas_HTML);
        } else {
            this.#tbody.innerHTML = filas_HTML;
        }

        document.querySelectorAll('.btn-generar-pdf').forEach(btn => {
            btn.onclick = (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                this.#generar_pdf(id);
            };
        });
    }



    async #generar_pdf(id) {

        const {datos} = await this._traer_datos(`./api.php?controller=reportes_controller&action=detalles_planilla&id=${id}`);
        
        if(!datos){
            this._notificacion('Error al traer datos', 'Error');
            return;
        } 

        const estudiante = datos.inscripcion;
        const rep = datos.representante_principal;
        const vista = await this._traer_html('./frontend/views/app/reportes/planilla.html');

        const ventana_impresion = window.open('', '_blank');
        ventana_impresion.document.write(vista);
        ventana_impresion.document.close();

        ventana_impresion.onload = () => {
        const d = ventana_impresion.document;

            d.getElementById('pl-periodo').textContent = estudiante.periodo;
            d.getElementById('pl-nivel').textContent = estudiante.nivel_academico;
            d.getElementById('pl-grado').textContent = estudiante.grado;
            d.getElementById('pl-seccion').textContent = estudiante.seccion;
            d.getElementById('pl-ingreso').textContent = estudiante.tipo_ingreso;

            d.getElementById('pl-nombres').textContent = `${estudiante.nombre_1} ${estudiante.nombre_2 || ''}`;
            d.getElementById('pl-apellidos').textContent = `${estudiante.apellido_1} ${estudiante.apellido_2 || ''}`;
            d.getElementById('pl-ci').textContent = estudiante.cedula_identidad ? `${estudiante.nacionalidad}-${estudiante.cedula_identidad}` : 'No posee';
            d.getElementById('pl-escolar').textContent = estudiante.cedula_escolar;
            d.getElementById('pl-nacimiento').textContent = estudiante.fecha_nacimiento;

            if (rep) {
                d.getElementById('pl-rep-nombre').textContent = `${rep.nombre_1} ${rep.apellido_1}`;
                d.getElementById('pl-rep-ci').textContent = `${rep.nacionalidad}-${rep.cedula_identidad}`;
                d.getElementById('pl-rep-parentesco').textContent = rep.parentesco;
            }

            setTimeout(() => { ventana_impresion.print(); }, 300);
        };
    }

    #exportar_reporte() {

        const params = new URLSearchParams();
        
        for (const [clave, valor] of Object.entries(this.#filtros_actuales)) {
            if (valor !== null && valor !== undefined && valor !== "") {
                params.append(clave, valor);
            }
        }

        const url = `./api.php?controller=reportes_controller&action=exportar_reportes&${params.toString()}`;        
        window.location.href = url;
    }
}
