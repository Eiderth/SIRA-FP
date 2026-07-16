import Utils from "../../../core/utils.js"

export default class Dashboard_controller extends Utils {
    
    #html;
 
    constructor() {
        super();
        this.#html = this._traer_html('./frontend/views/app/dashboard/index.html');
    }

    async init() {
        const html = await this.#html;
        
        this._inyectar_html( document.getElementById('root-app'), html);

    }
}