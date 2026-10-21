import Utils from "../../../core/utils.js"

export default class Dashboard_controller extends Utils {
    
    #html;
    _cambiar_interfaz;

    constructor(cambiar_interfaz) {
        super();
        this.#html = this._traer_html('./frontend/views/app/dashboard/index.html');
        this._cambiar_interfaz = cambiar_interfaz;
    }

    async init() {
        const html = await this.#html;
        await this._inyectar_html(document.getElementById('root-app'), html);
        
        const usuario = JSON.parse(localStorage.getItem('sesion'));
        this.#llenar_data_usuario(usuario);
        this.#cargar_datos_dashboard();
        this.#dar_eventos_click();
    }

    #llenar_data_usuario(usuario) {
        if (!usuario) return;
        document.getElementById('info-letra-usuario').textContent = usuario.nombre ? usuario.nombre.charAt(0).toUpperCase() : '-';
        document.getElementById('info-usuario').textContent = usuario.nombre || 'Usuario';
        document.getElementById('info-rol').textContent = usuario.rol || '';
    }

    async #cargar_datos_dashboard() {
        const respuesta = await this._traer_datos('./api.php?controller=dashboard_controller&action=obtener_datos_resumen');

        if (!respuesta) return;

        // 1. Tarjetas Superiores (KPIs)
        this.#poblar_tarjetas_kpi(respuesta);

        // 2. Perfil Socioeconómico y Laboral
        this.#poblar_perfil_socioeconomico(respuesta.rangos_ingresos, respuesta.top_profesiones);

        // 3. Matrícula por Secciones
        this.#poblar_estudiantes_secciones(respuesta.estudiantes_por_seccion);

        // 4. Salud, Antropometría y Deportes / Canaima
        this.#poblar_salud_antropometria_deportes(respuesta.salud, respuesta.promedios_antropometricos, respuesta.deportes, respuesta.canaimas);

        // 5. Hábitat Estudiantil / Resumen Geográfico
        this.#poblar_resumen_geografico(respuesta.top_sectores, respuesta.tipos_vivienda, respuesta.condiciones_vivienda);

        // 6. Últimas Inscripciones
        this.#poblar_ultimas_inscripciones(respuesta.resumen_reportes);
    }

    #poblar_tarjetas_kpi(datos) {
        if (datos.estudiantes_activos !== undefined) {
            document.getElementById('estudiantes-activos').textContent = datos.estudiantes_activos;
        }
        if (datos.periodo_activo !== undefined) {
            document.getElementById('periodo-activo').textContent = datos.periodo_activo;
        }
        if (datos.grados_secciones_activas !== undefined) {
            document.getElementById('grados-secciones-activas').textContent = datos.grados_secciones_activas;
        }
        if (datos.promedio_ingresos !== undefined) {
            document.getElementById('promedio-ingresos').textContent = `$ ${Number(datos.promedio_ingresos).toFixed(2)}`;
        }
    }

    #poblar_perfil_socioeconomico(rangos_ingresos = [], top_profesiones = []) {
        const contenedor_rangos = document.getElementById('lista-rangos-ingresos');
        if (contenedor_rangos) {
            if (rangos_ingresos.length === 0) {
                contenedor_rangos.innerHTML = `<li class="list-group-item text-muted px-0 bg-transparent py-1">Sin registros</li>`;
            } else {
                contenedor_rangos.innerHTML = rangos_ingresos.map(rango => `
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent py-1">
                        <span class="text-secondary">${rango.etiqueta}</span>
                        <strong class="text-dark">${rango.cantidad}</strong>
                    </li>
                `).join('');
            }
        }

        const contenedor_profesiones = document.getElementById('lista-top-profesiones');
        if (contenedor_profesiones) {
            if (top_profesiones.length === 0) {
                contenedor_profesiones.innerHTML = `<li class="list-group-item text-muted px-0 bg-transparent py-1">Sin registros</li>`;
            } else {
                contenedor_profesiones.innerHTML = top_profesiones.map(item => `
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent py-1">
                        <span class="text-secondary text-truncate" style="max-width: 140px;">${item.profesion || 'No especificada'}</span>
                        <strong class="text-dark">${item.total}</strong>
                    </li>
                `).join('');
            }
        }
    }

    #poblar_estudiantes_secciones(estudiantes_por_seccion = []) {
        const tbody = document.getElementById('tabla-estudiantes-secciones');
        if (!tbody) return;

        if (estudiantes_por_seccion.length === 0) {
            tbody.innerHTML = `<tr><td colspan="3" class="text-center py-3 text-muted">Sin datos de matrícula</td></tr>`;
            return;
        }

        tbody.innerHTML = estudiantes_por_seccion.map(item => `
            <tr>
                <td class="ps-3 py-2 fw-semibold text-dark">${item.grado} "${item.seccion}"</td>
                <td class="text-center py-2 fw-bold text-primary">${item.total_estudiantes}</td>
                <td class="text-end pe-3 py-2 text-muted">${item.porcentaje_distribucion || 0}%</td>
            </tr>
        `).join('');
    }

    #poblar_salud_antropometria_deportes(salud = {}, promedios = {}, deportes = {}, canaimas = {}) {
        document.getElementById('salud-atencion-especial').textContent = salud.atencion_especial ?? '--';
        document.getElementById('salud-alergias').textContent = salud.alergias ?? '--';
        document.getElementById('salud-especialista').textContent = salud.bajo_especialista ?? '--';

        document.getElementById('promedio-talla-camisa').textContent = promedios.talla_camisa || '--';
        document.getElementById('promedio-talla-pantalon').textContent = promedios.talla_pantalon || '--';
        document.getElementById('promedio-talla-zapato').textContent = promedios.talla_zapato || '--';

        document.getElementById('deportes-porcentaje').textContent = deportes.porcentaje_participacion ? `${deportes.porcentaje_participacion}%` : '-- %';
        document.getElementById('canaimas-operativas').textContent = canaimas.operativas ?? '--';
    }

    #poblar_resumen_geografico(top_sectores = [], tipos_vivienda = [], condiciones_vivienda = []) {
        const renderizar_lista = (id_elemento, lista, clave_nombre, clave_valor) => {
            const contenedor = document.getElementById(id_elemento);
            if (!contenedor) return;
            if (lista.length === 0) {
                contenedor.innerHTML = `<li class="list-group-item text-muted px-0 bg-transparent py-1">Sin datos</li>`;
                return;
            }
            contenedor.innerHTML = lista.map(item => `
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent py-1">
                    <span class="text-secondary text-truncate" style="max-width: 140px;">${item[clave_nombre] || 'N/E'}</span>
                    <strong class="text-dark">${item[clave_valor]}</strong>
                </li>
            `).join('');
        };

        renderizar_lista('lista-top-sectores', top_sectores, 'sector', 'total');
        renderizar_lista('lista-tipos-vivienda', tipos_vivienda, 'tipo_vivienda', 'total');
        renderizar_lista('lista-condicion-vivienda', condiciones_vivienda, 'condicion_vivienda', 'total');
    }

    #poblar_ultimas_inscripciones(resumen_reportes = []) {
        const tbody = document.getElementById('resumen-reportes');
        if (!tbody) return;

        if (resumen_reportes.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center py-4 text-body-secondary">
                        <i class="bi bi-inbox fs-2 d-block mb-2 text-opacity-50"></i>
                        No hay inscripciones recientes registradas.
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = resumen_reportes.map(r => {
            const cedula = r.cedula_identidad && r.cedula_identidad !== '' 
                ? `${r.nacionalidad || 'V'}-${r.cedula_identidad}` 
                : (r.cedula_escolar || 'N/P');

            const nombre_completo = `${r.nombre_1 || ''} ${r.apellido_1 || ''}`.trim();
            const grado_seccion = `${r.grado || ''} ${r.seccion ? '"' + r.seccion + '"' : ''}`.trim();

            return `
                <tr>
                    <td class="ps-4 fw-bold text-dark">${nombre_completo}</td>
                    <td class="font-monospace fw-semibold text-secondary">${cedula}</td>
                    <td><span class="badge bg-light text-dark border">${grado_seccion}</span></td>
                    <td class="text-muted small">${r.fecha_inscripcion || '--'}</td>
                </tr>`;
        }).join('');
    }

    #dar_eventos_click() {
        const elementos_click = [
            { id: 'cont-estudiantes-activos', interfaz: 'buscar' },
            { id: 'cont-periodo-activo', interfaz: 'periodos' },
            { id: 'cont-grados-secciones-activas', interfaz: 'vinculaciones' },
            { id: 'btn-table-reportes', interfaz: 'reportes' }
        ];

        elementos_click.forEach(item => {
            const el = document.getElementById(item.id);
            if (el) {
                el.addEventListener('click', () => {
                    this._cambiar_interfaz(item.interfaz);
                });
            }
        });
    }
}