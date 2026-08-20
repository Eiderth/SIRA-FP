import Utils from "../../core/utils.js";

export default class Login_controller extends Utils  {
    #root;
    #html
    
    #data;
    cambio_interfaz
    #btn_ingresar;
    #formulario_login;

    constructor(cambio_interfaz) {
        super();
        this.#html = this._traer_html('./frontend/views/login/index.html');
        this.cambio_interfaz = cambio_interfaz
        this.#root = document.getElementById('root')
    }

    async init() {
        
        const html = await this.#html;
        await this._inyectar_html(this.#root, html);
        
        this.#formulario_login  = document.getElementById('formulario-login');
        this.#btn_ingresar = document.getElementById('btn-ingresar')

        this.#btn_ingresar.addEventListener('click', () => this.#enviar_formulario());
    }

    async #enviar_formulario() {
        
        if(!this._validar_formulario(this.#formulario_login)){
            this._notificacion('Debe llenar todos los campos')
            return;
        };

        this.#data = Object.fromEntries(new FormData(this.#formulario_login));
        
        this.#btn_ingresar.disabled = true;
        
        const resp = await this._enviar_datos('./api.php?controller=login_controller&action=iniciar_sesion', this.#data);

        if (resp.acceso == true) {
            localStorage.setItem("sesion", JSON.stringify(resp.usuario));
            this._notificacion("Bienvenido nuevo usuario");
            this.cambio_interfaz('app');

        } else {
            this._notificacion(resp.mensaje ?? 'Ha ocurrido un error');
        }

        this.#btn_ingresar.disabled = false
    }


}