import Utils from "../../core/utils.js";

export default class Login_controller extends Utils {
    #root;
    #html;
    cambio_interfaz;
    #btn_ingresar;
    #formulario_login;
    #crear_cuenta = false; 

    constructor(cambio_interfaz) {
        super();
        this.cambio_interfaz = cambio_interfaz;
        this.#html = this._traer_html('./frontend/views/login/index.html');
        this.#root = document.getElementById('root');
    }

    async init() {
        const html = await this.#html;
        await this._inyectar_html(this.#root, html);

        this.#crear_cuenta = false;
        await this.#verificar_db();

        this.#formulario_login = document.getElementById('formulario-login');
        this.#btn_ingresar = document.getElementById('btn-ingresar');
        this.#btn_ingresar.addEventListener('click', () => this.#iniciar_session());

        // Botón para ver contraseña principal
        document.getElementById('btn-ver-pass')?.addEventListener('click', () => {
            const inputPass = document.getElementById('input-pass');
            const iconoPass = document.getElementById('icono-pass');
            
            if (inputPass) inputPass.type = 'text';
            if (iconoPass) iconoPass.classList.replace('bi-eye-slash', 'bi-eye');

            setTimeout(() => {
                document.getElementById('icono-pass')?.classList.replace('bi-eye', 'bi-eye-slash');
                document.getElementById('input-pass')?.setAttribute('type', 'password');
            }, 3000);
        });

        // Botón para ver confirmación de contraseña
        document.getElementById('btn-ver-confirmar-pass')?.addEventListener('click', () => {
            const inputConfirm = document.getElementById('input-confirmar-pass');
            const iconoConfirm = document.getElementById('icono-confirmar-pass');

            if (inputConfirm) inputConfirm.type = 'text';
            if (iconoConfirm) iconoConfirm.classList.replace('bi-eye-slash', 'bi-eye');

            setTimeout(() => {
                document.getElementById('icono-confirmar-pass')?.classList.replace('bi-eye', 'bi-eye-slash');
                document.getElementById('input-confirmar-pass')?.setAttribute('type', 'password');
            }, 3000);
        });
    }

    async #verificar_db() {
        const {existen_usuarios} = await this._traer_datos('./api.php?controller=login_controller&action=existen_usuarios');
        
        if (!existen_usuarios) {
            document.getElementById('title-form-login').textContent = 'Crear Cuenta';
            document.getElementById('contenedor-confirmar-pass').classList.replace('d-none', 'd-block');
            document.getElementById('input-confirmar-pass').disabled = false;;
            
            this.#crear_cuenta = true;
        } 
    }

    async #iniciar_session() {
        if (!this._validar_formulario(this.#formulario_login)) {
            this._notificacion('Debe llenar todos los campos');
            return;
        }

        const data = Object.fromEntries(new FormData(this.#formulario_login));

        for (let clave in data) {
            if (data[clave].trim() === "") {
                this._notificacion("Rellene todos los campos");
                return;
            } else {
                data[clave] = data[clave].trim();
            }
        }

        if (this.#crear_cuenta && data.pass !== data.confirmar_pass) {
            this._notificacion("Ha ingresado valores distintos en la contraseña");
            return;
        }

        this.#btn_ingresar.disabled = true;

        const action = this.#crear_cuenta ? 'crear_cuenta' : 'iniciar_sesion';
        const { acceso, usuario = null } = await this._enviar_datos(`./api.php?controller=login_controller&action=${action}`, data);

        if (acceso === true && usuario && Object.keys(usuario).length !== 0) {
            this.#formulario_login.reset();
            localStorage.setItem("sesion", JSON.stringify(usuario));
            this._notificacion(`Bienvenido usuario: ${usuario.nombre} rol: ${usuario.rol}`, 'Session iniciada', 4000);
            this.cambio_interfaz('app');
        }

        this.#btn_ingresar.disabled = false;
    }
}