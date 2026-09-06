import Utils from "../../../core/utils.js"

export default class Busqueda_controller extends Utils {
    
    #html;

    #form_busqueda;
    #btn_buscar
    #select_periodo;
    #select_grado;
    #select_seccion;
    #btn_limpiar_filtros;
    #tbody_resultados_estudiantes;
    #badge_total_resultados;

 
    constructor() {
        super();
        this.#html = this._traer_html('./frontend/views/app/busqueda/index.html');
    }

    async init() {
        const html = await this.#html;        
        await this._inyectar_html( document.getElementById('root-app'), html);
        this.#form_busqueda = document.getElementById('form-busqueda');
        this.#btn_buscar = document.getElementById('btn-buscar');
        this.#select_periodo = document.getElementById('select-periodo');
        this.#select_grado = document.getElementById('select-grado');
        this.#select_seccion = document.getElementById('select-seccion');
        this.#btn_limpiar_filtros = document.getElementById('btn-limpiar-filtros');
        this.#tbody_resultados_estudiantes = document.getElementById('tbody-resultados-estudiantes');
        this.#badge_total_resultados = document.getElementById('badge-total-resultados');

        this.#insertar_parametros();
        this.#dar_eventos();
    }

    async #insertar_parametros() {
        const {periodos, grados, secciones}  = (await this._traer_datos('./api.php?controller=busqueda_controller&action=traer_parametros')).data;
        this._llenar_select(this.#select_periodo, [{ 'nombre' : 'Todos los periodos', 'id': ''}, ...periodos]);
        this._llenar_select(this.#select_grado, [{'nombre': 'Todos los grados', 'id': ''}, ...grados.grados]);
        this._llenar_select(this.#select_seccion, [ {'nombre': 'Todas las secciones', 'id': ''}, ...secciones]);
    }

    async #dar_eventos() {
        this.#btn_buscar.addEventListener('click', async () => {
            this.#btn_buscar.disabled = true;
            const data = Object.fromEntries(new FormData(this.#form_busqueda));
            this._limpiar_objeto(data)
            const { resultados } = await this._enviar_datos('./api.php?controller=busqueda_controller&action=buscar_coincidencias', data);
            
            this.#llenar_tablas(resultados.estudiantes);
            setTimeout(()=>{
                this.#btn_buscar.disabled = false;
            }, 500);
        });

        this.#btn_limpiar_filtros.addEventListener('click', () => {
            this.#select_periodo.querySelector('option[value=""]').selected = true;
            this.#select_grado.querySelector('option[value=""]').selected = true;
            this.#select_seccion.querySelector('option[value=""]').selected = true;
            document.getElementById('input-busqueda-general').value = '';
        });
    }

    #llenar_tablas(estudiantes) {
        if(estudiantes.length > 0){            
            let tbody_estudiantes = '';
            estudiantes.forEach(e => {
                tbody_estudiantes += `
                    <tr>
                        <td class="ps-3 py-3 fw-semibold text-secondary">${e.est_cedula_identidad ? e.est_nacionalidad+'-': ''}${e.est_cedula_identidad || e.est_cedula_escolar}</td>
                        <td class="py-3 fw-bold text-dark">${e.est_nombre_1} ${e.est_nombre_2?.charAt(0) ?? ''} ${e.est_apellido_1} ${e.est_apellido_2?.charAt(0)?? ''}</td>                    
                        <td class="py-3">
                            <span class="d-block text-dark fs-7">${e.rep_nombre_1} ${e.rep_apellido_1}</span>
                            <small class="text-muted">${e.rep_telefono_movil ?? ''}</small>
                        </td>
                        <td class="py-3 text-center">
                            <span class="${e.est_estado == 'Activo' ? 'text-primary': 'text-secondary'} fw-semibold">${e.est_estado}</span>
                        </td>
                        <td class="pe-3 py-3 text-end">
                            <button type="button" data-id="${e.estudiante_id}" class="btn-buscar-data-estudiante btn btn-primary btn-sm text-nowrap">ver datos</button>
                        </td>
                    </tr>
                `
            });
            this.#tbody_resultados_estudiantes.innerHTML = tbody_estudiantes;

            document.querySelectorAll('.btn-buscar-data-estudiante').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const id = e.currentTarget.getAttribute('data-id');
                    if(!id) return;

                    const resp = await this._enviar_datos('./api.php?controller=busqueda_controller&action=buscar_datos_estudiante', {'estudiante_id': id});

                    if(resp.estado == 'completado'){
                        this.#mostrar_expediente(resp.resultados);
                        return;
                    } 
                    this._notificacion('algo ha salido mal')
                });
            })

        } else {
            this.#tbody_resultados_estudiantes.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-3 text-muted">
                        <i class="bi bi-search fs-4 d-block mb-1 opacity-50"></i>
                        <span class="fs-7">Ingrese un término o active un filtro para consultar estudiantes.</span>
                    </td>
                </tr>
            `
        } 
        this.#badge_total_resultados.textContent = `${estudiantes.length} Registros`;
    }

    #mostrar_expediente(data) {
        const modalEl = document.getElementById('modalExpedienteEstudiante');
        const tabRepSecundario = document.getElementById('item-tab-rep-secundario');

        const val = (valor, fallback = 'No registra') => (valor !== null && valor !== undefined && valor !== '') ? valor : fallback;

        const mapeo = {
            // Encabezado e identidades
            'estudiante_nombre_completo': `${data.estudiante.persona.nombre_1} ${data.estudiante.persona.nombre_2 || ''} ${data.estudiante.persona.apellido_1} ${data.estudiante.persona.apellido_2 || ''}`.trim(),
            'estudiante_cedula_completa': `${data.estudiante.persona.nacionalidad}-${data.estudiante.persona.cedula_identidad}`,
            'estudiante_cedula_escolar': val(data.estudiante.estudiante.cedula_escolar),
            'estudiante_estado': val(data.estudiante.estudiante.estado),

            // Estudiante Persona
            'persona.nombre_1': val(data.estudiante.persona.nombre_1),
            'persona.nombre_2': val(data.estudiante.persona.nombre_2, 'N/A'),
            'persona.apellido_1': val(data.estudiante.persona.apellido_1),
            'persona.apellido_2': val(data.estudiante.persona.apellido_2, 'N/A'),
            'persona.fecha_nacimiento': val(data.estudiante.persona.fecha_nacimiento),
            'persona.sexo': data.estudiante.persona.sexo === 'M' ? 'Masculino' : 'Femenino',
            'estudiante.lateralidad': val(data.estudiante.estudiante.lateralidad),
            'estudiante.numero_hijo': val(data.estudiante.estudiante.numero_hijo),

            // Antropometría
            'antropometricos.estatura': data.estudiante.antropometricos.estatura ? `${data.estudiante.antropometricos.estatura} cm` : 'No registra',
            'antropometricos.peso': data.estudiante.antropometricos.peso ? `${data.estudiante.antropometricos.peso} kg` : 'No registra',
            'antropometricos.talla_camisa': val(data.estudiante.antropometricos.talla_camisa),
            'antropometricos.talla_pantalon': val(data.estudiante.antropometricos.talla_pantalon),
            'antropometricos.talla_zapato': val(data.estudiante.antropometricos.talla_zapato),

            // Salud
            'salud.reacciones_alergicas': val(data.estudiante.salud.reacciones_alergicas),
            'salud.cuales_alergias': val(data.estudiante.salud.cuales_alergias),
            'salud.enfermedades_padecidas': val(data.estudiante.salud.enfermedades_padecidas),
            'salud.atencion_especial': val(data.estudiante.salud.atencion_especial),
            'salud.atendido_por_especialista': val(data.estudiante.salud.atendido_por_especialista),
            'salud.nombre_especialista': val(data.estudiante.salud.nombre_especialista),

            // Extracurricular / Canaima
            'extra_curriculares.realiza_deportes': val(data.estudiante.extra_curriculares.realiza_deportes),
            'extra_curriculares.cuales_deportes': val(data.estudiante.extra_curriculares.cuales_deportes),
            'extra_curriculares.posee_canaima': val(data.estudiante.extra_curriculares.posee_canaima),
            'extra_curriculares.estado_canaima': val(data.estudiante.extra_curriculares.estado_canaima),
            'extra_curriculares.serial_canaima': val(data.estudiante.extra_curriculares.serial_canaima),
            'extra_curriculares.posee_cargador': val(data.estudiante.extra_curriculares.posee_cargador),
            'extra_curriculares.estado_cargador': val(data.estudiante.extra_curriculares.estado_cargador),

            // Representante Principal
            'rep_nombre_completo': `${data.representante_principal.representante.nombre_1} ${data.representante_principal.representante.nombre_2 || ''} ${data.representante_principal.representante.apellido_1} ${data.representante_principal.representante.apellido_2 || ''}`.trim(),
            'rep_cedula_completa': `${data.representante_principal.representante.nacionalidad}-${data.representante_principal.representante.cedula_identidad}`,
            'representante_principal.persona.parentesco': val(data.representante_principal.persona.parentesco),
            'representante_principal.persona.telefono_movil': val(data.representante_principal.persona.telefono_movil),
            'representante_principal.persona.telefono_habitacion': val(data.representante_principal.persona.telefono_habitacion),
            'representante_principal.persona.correo_electronico': val(data.representante_principal.persona.correo_electronico),
            'representante_principal.persona.profesion': val(data.representante_principal.persona.profesion),
            'representante_principal.persona.estado_civil': val(data.representante_principal.persona.estado_civil),
            'representante_principal.persona.empresa_trabajo': val(data.representante_principal.persona.empresa_trabajo)
        };

        if (data.representante_secundario && data.representante_secundario.representante) {
            tabRepSecundario.classList.remove('d-none');
            
            const repSec = data.representante_secundario;
            Object.assign(mapeo, {
                'rep_secundario_nombre_completo': `${repSec.representante.nombre_1} ${repSec.representante.nombre_2 || ''} ${repSec.representante.apellido_1} ${repSec.representante.apellido_2 || ''}`.trim(),
                'rep_secundario_cedula_completa': `${repSec.representante.nacionalidad}-${repSec.representante.cedula_identidad}`,
                'representante_secundario.persona.parentesco': val(repSec.persona.parentesco),
                'representante_secundario.persona.telefono_movil': val(repSec.persona.telefono_movil),
                'representante_secundario.persona.telefono_habitacion': val(repSec.persona.telefono_habitacion),
                'representante_secundario.persona.correo_electronico': val(repSec.persona.correo_electronico),
                'representante_secundario.persona.profesion': val(repSec.persona.profesion),
                'representante_secundario.persona.estado_civil': val(repSec.persona.estado_civil),
                'representante_secundario.persona.empresa_trabajo': val(repSec.persona.empresa_trabajo)
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