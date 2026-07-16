import Utils from "../../../core/utils.js"

export default class Usuarios_controller extends Utils {
    #html;

    #tbody;
    #formulario;
    #btn_crear_usuario
    #mensaje;

    constructor() {
        super();
        this.#html = this._traer_html('./frontend/views/app/usuarios/index.html');
    }

    async init(){
        const html = await this.#html;
        await this._inyectar_html(document.getElementById('root-app'), html);
        this.#tbody = document.getElementById('tabla-usuarios-body');
        this.#formulario = document.getElementById('formulario-crear-usuario');
        this.#btn_crear_usuario = document.getElementById('btn-crear-usuario');
        this.#mensaje = document.getElementById('mensaje-admin');
        this.#cargar_usuarios();
        this.#btn_crear_usuario.addEventListener('click', () => this.#crear_usuario());

    }

    async #cargar_usuarios(){

        this.#tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center py-4 text-secondary">
                    <div class="spinner-border spinner-border-sm me-2 text-primary" role="status"></div>
                    Cargando usuarios...
                </td>
            </tr>`;

        const resp = await this._traer_datos('./api.php?controller=usuarios_controller&action=listar');

        if (resp.estado !== 'completado') {
            this.#tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-danger">Error al cargar usuarios</td></tr>`;
            return;
        }

        if (resp.usuarios.length === 0) {
            this.#tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-secondary">No hay usuarios registrados</td></tr>`;
            return;
        }

        this.#tbody.innerHTML = resp.usuarios.map(u => `
            <tr>
                <td class="ps-4 py-3">${u.id}</td>
                <td class="py-3">${u.nombre}</td>
                <td class="py-3"><span class="badge ${u.rol === 'Administrador' ? 'bg-warning text-dark' : u.rol === 'Secretaria' ? 'bg-info text-dark' : 'bg-secondary'}">${u.rol}</span></td>
                <td class="pe-4 py-3 text-end"><span class="badge ${u.estado === 'Activo' ? 'bg-success' : 'bg-danger'}">${u.estado}</span></td>
            </tr>`
        ).join('');

    }

    async #crear_usuario(){

        const data = Object.fromEntries(new FormData(this.#formulario));

        const resp = await this._enviar_datos('./api.php?controller=usuarios_controller&action=crear', data);

        if (resp.estado === 'completado') {
            this.#mensaje.innerHTML = `<div class="alert alert-success py-2 m-0">${resp.mensaje}</div>`;
            this.#formulario.reset();
            await this.#cargar_usuarios();
        } else {
            this.#mensaje.innerHTML = `<div class="alert alert-danger py-2 m-0">${resp.mensaje}</div>`;
        }

        setTimeout(() => this.#mensaje.innerHTML = '', 4000);
    }
}
