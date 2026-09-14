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

    async init() {
        const {acceso, usuario = null} = await this._traer_datos('./api.php?controller=router&action=validar_session');
        
        if (acceso && usuario && Object.keys(usuario).length !== 0) {
            localStorage.setItem("sesion", JSON.stringify(usuario));
            this._notificacion(`Bienvenido de nuevo usuario: ${usuario.nombre} rol: ${usuario.rol}`, 'Session iniciada', 4000);
            this.cambio_interfaz('app');
        } else {
            this.cambio_interfaz('login');
        }
    }

    cambio_interfaz(interfaz) {

        switch(interfaz) {
            case 'login':
                this.#login_controller.init();
                break;
            case 'app':
                this.#app_controller.init();
                break;

            default: this._notificacion('esa interfaz no existe', 'Error');
        }

    }
}