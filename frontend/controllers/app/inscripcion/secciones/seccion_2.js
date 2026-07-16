import Utils from "../../../../core/utils.js";

export default class Seccion_2_controller extends Utils{

    #html;

    constructor() {
        super();
        this.#html = this._traer_html('./frontend/views/app/formulario-inscripcion/secciones/seccion_2.html');
    }

    async init(data) {
        const html = await this.#html;
        await this._inyectar_html(document.getElementById('secciones-root'), html);
        document.getElementById('btn-atras').classList.remove('d-none');
        if(data) this._llenar_inputs(data);
    }
}