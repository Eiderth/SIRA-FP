import Utils from "../../../../core/utils.js";

export default class Seccion_error extends Utils{

    #html;

    constructor() {
        super();
        this.#html = this._traer_html('./frontend/views/app/formulario-inscripcion/interfaz-final/error.html');
    }

    async init(mensaje, cambio_interfaz) {
        const html = await this.#html;
        await this._inyectar_html(document.getElementById('secciones-root'), html)

        document.getElementById('info').classList.add('d-none');
        document.getElementById('botonera').classList.replace('d-flex','d-none');
        
        document.getElementById('mensaje-tecnico').innerHTML = mensaje;

        document.getElementById('btn-volver').addEventListener('click',() => cambio_interfaz());

    }
}