import Utils from "../../../core/utils.js"


export default class Reportes_controlller extends Utils {
    #html;

    #data;

    #badgeTotal;
    #tbody;

    constructor() {
        super();
        this.#html = this._traer_html('./frontend/views/app/reportes/index.html');
    } 

    async init() {
        const html = await this.#html;
        
        await this._inyectar_html(document.getElementById('root-app'), html);
        
        this.#tbody = document.getElementById('tabla-inscritos-body');
        this.#badgeTotal = document.getElementById('total-inscritos');
        
        this.#data = await this._traer_datos('./api.php?controller=reportes_controller&action=listar');

        this.#cargar_inscritos();
        this.#eventos_click();
    }

    #cargar_inscritos() {

        if (!this.#data.datos || this.#data.datos.length === 0) {
            this.#tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4">No hay estudiantes inscritos.</td></tr>`;
            this.#badgeTotal.textContent = '0 Alumnos';
            return;
        }

        this.#badgeTotal.textContent = `${this.#data.datos.length} Alumnos`;
        this.#tbody.innerHTML = '';

        this.#data.datos.forEach(inscrito => {
            const tr = document.createElement('tr');
            const doc = inscrito.cedula_identidad ? `${inscrito.nacionalidad}-${inscrito.cedula_identidad}` : inscrito.cedula_escolar;
            const nombre = `${inscrito.nombre_1} ${inscrito.apellido_1}`;
            
            tr.innerHTML = `
                <td class="fw-semibold ps-4">${doc}</td>
                <td class="text-dark fw-bold">${nombre}</td>
                <td class="text-muted">${inscrito.fecha_inscripcion}</td>
                <td class="text-center pe-4">
                    <button data-id="${inscrito.id_inscripcion}" id="btn-generar-pdf" class="btn btn-outline-danger btn-sm px-3 fw-bold" >
                        📄 PDF
                    </button>
                </td>
            `;
            this.#tbody.appendChild(tr);
        });
    }

    #eventos_click() {
        const btns =  document.querySelectorAll('#btn-generar-pdf');

        btns.forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.target.dataset.id

                const data = await this._traer_datos(`./api.php?controller=reportes_controller&action=detalles_planilla&id=${id}`);
                
                if(!data.datos){
                    this._notificacion('Error al traer datos');
                    return;
                } 

                const estudiante = data.datos.inscripcion;
                const rep = data.datos.representante_principal;
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

            })
        })                        
    }

}
