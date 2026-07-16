import Utils from "../../../../core/utils.js";

export default class Seccion_exito extends Utils{

    #html;

    constructor() {
        super();
        this.#html = this._traer_html('./frontend/views/app/formulario-inscripcion/interfaz-final/exito.html');
    }

    async init(data, cambio_interfaz) {
        const html = await this.#html;
        await this._inyectar_html(document.getElementById('secciones-root'), html)

        document.getElementById('info').classList.add('d-none');
        document.getElementById('botonera').classList.replace('d-flex','d-none');

        const mensaje_tecnico =  `Bienvenido joven ${data.data_estudiante.nombre_1.charAt(0)} ${data.data_estudiante.apellido_1} 🎓`;
        const lista_tecnica = `<li>Inscripción N# ${data.numero_inscripcion}</li> <li>Cédula Escolar: ${data.cedula_escolar}</li>`;
        document.getElementById('mensaje-tecnico').textContent = mensaje_tecnico;
        document.getElementById('lista-tecnica').innerHTML = lista_tecnica;
        document.getElementById('btn-volver').addEventListener('click',() => cambio_interfaz());
    }
}