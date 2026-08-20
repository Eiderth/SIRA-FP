import Utils from "../core/utils.js";
import Login_controller from "./login/index.js";
import App_controller from "./app/index.js";


export default class Enrutador extends Utils {

    #login_controller;
    #app_controller
    
    constructor() {
        super();
        this.#login_controller = new Login_controller((interfaz) => this.cambio_interfaz(interfaz));
        this.#app_controller = new App_controller((interfaz) => this.cambio_interfaz(interfaz));
    }

    init() {
        this.#login_controller.init();
    }

    cambio_interfaz(interfaz) {

        switch(interfaz) {
            case 'login':
                window.location.reload();
                break;
            case 'app':
                this.#app_controller.init();
                break;

            default: this._notificacion('esa interfaz no existe');
        }

    }
}