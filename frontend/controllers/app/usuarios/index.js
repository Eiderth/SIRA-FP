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

        const {usuarios} = await this._traer_datos('./api.php?controller=usuarios_controller&action=listar_usuarios');

        if (!usuarios) {
            this.#tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-danger">Error al cargar usuarios</td></tr>`;
            return;
        }

        if (usuarios && usuarios.length === 0) {
            this.#tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-secondary">No hay usuarios registrados</td></tr>`;
            return;
        }
        
        this.#tbody.innerHTML = usuarios.map(u => `
            <tr>
                <td class="ps-4 py-3">${u.id}</td>
                <td class="py-3">${u.nombre}</td>
                <td class="py-3"><span class="badge ${u.rol === 'Administrador' ? 'bg-warning text-dark' : u.rol === 'Secretaria' ? 'bg-info text-dark' : 'bg-secondary'}">${u.rol}</span></td>
                <td class="py-3"><span class="badge ${u.estado === 'Activo' ? 'bg-success' : 'bg-danger'}">${u.estado}</span></td>
                <td class="text-end pe-3 py-2">
                    <button type="button" data-id=${u.id} title="Eliminar" class="btn-eliminar-usuario btn btn-outline-danger btn-sm py-0 px-2 rounded-2"><i class="bi bi-trash-fill fs-7"></i></button>
                </td>
            </tr>`
        ).join('');

        document.querySelectorAll('.btn-eliminar-usuario').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                this.#eliminar_usuario(id);
            })
        });

    }

    async #eliminar_usuario(id) {
        const {eliminado} = await this._enviar_datos('./api.php?controller=usuarios_controller&action=eliminar_usuario', {'id': id}); 
        if (eliminado) {
            this._notificacion('Usuario eliminado con exito', 'Usuario eliminado');
            this.#cargar_usuarios();
            return;
        } 

    }

    async #crear_usuario(){

        const data = Object.fromEntries(new FormData(this.#formulario));

        const {usuario} = await this._enviar_datos('./api.php?controller=usuarios_controller&action=crear_usuario', data);

        if (usuario && Object.keys(usuario) !== 0) {
            this._notificacion(`Se agrego al usuario ${usuario.nombre} al sistema`, 'Usuario creado')
            this.#formulario.reset();
            await this.#cargar_usuarios();
        } 

        setTimeout(() => this.#mensaje.innerHTML = '', 4000);
    }
}
