import Utils from "./utils.js";
import Login_controller from "../controllers/login/index.js";
import App_controller from "../controllers/app/index.js";


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
                this.#login_controller.init();
                break;
            case 'app':
                this.#app_controller.init();
                break;

            default: this._notificacion('esa interfaz no existe');
        }

    }
}