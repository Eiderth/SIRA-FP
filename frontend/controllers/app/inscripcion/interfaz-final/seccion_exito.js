import Utils from "../../../../core/utils.js";

export default class Seccion_exito extends Utils{

    #html;

    constructor() {
        super();
        this.#html = this._traer_html('./frontend/views/app/formulario-inscripcion/interfaz-final/exito.html');
    }

    async init(estudiante, llave_inscripcion, cedula_escolar, cambio_interfaz) {
        const html = await this.#html;
        await this._inyectar_html(document.getElementById('secciones-root'), html)

        document.getElementById('info').classList.add('d-none');
        document.getElementById('botonera').classList.replace('d-flex','d-none');

        const mensaje_tecnico =  `Bienvenido joven ${estudiante.persona.nombre_1.charAt(0)} ${estudiante.persona.apellido_1} 🎓`;
        const lista_tecnica = `<li>Inscripción N# ${llave_inscripcion}</li> <li>Cédula Escolar: ${cedula_escolar}</li>`;

        document.getElementById('mensaje-tecnico').textContent = mensaje_tecnico;
        document.getElementById('lista-tecnica').innerHTML = lista_tecnica;
        document.getElementById('btn-volver').addEventListener('click',() => cambio_interfaz());
    }
}