import Utils from "../../../core/utils.js"

export default class Usuarios_controller extends Utils {
    #html;

    #tbody;
    #formulario_crear_usuario;
    #btn_crear_usuario;
    #formulario_filtrar_usuarios;
    #btn_filtrar_usuarios;
    #btn_resetear_tabla;

    constructor() {
        super();
        this.#html = this._traer_html('./frontend/views/app/usuarios/index.html');
    }

    async init(){
        const html = await this.#html;
        await this._inyectar_html(document.getElementById('root-app'), html);
        
        this.#tbody = document.getElementById('tabla-usuarios-body');
        this.#formulario_crear_usuario = document.getElementById('formulario-crear-usuario');
        this.#btn_crear_usuario = document.getElementById('btn-crear-usuario');

        this.#formulario_filtrar_usuarios = document.getElementById('formulario-filtrar-usuarios');
        this.#btn_filtrar_usuarios = document.getElementById('btn-filtrar-usuarios');
        
        this.#btn_resetear_tabla = document.getElementById('btn-resetear-tabla');
        
        this.#cargar_usuarios();

        this.#btn_crear_usuario.addEventListener('click', () => this.#crear_usuario());

        this.#btn_filtrar_usuarios.addEventListener('click', () => this.#filtrar_usuarios());

        this.#btn_resetear_tabla.addEventListener('click', () => this.#resetear());

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

    async #cargar_usuarios(usuarios = null){

        this.#tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center py-4 text-secondary">
                    <div class="spinner-border spinner-border-sm me-2 text-primary" role="status"></div>
                    Cargando usuarios...
                </td>
            </tr>`;

        usuarios ||= (await this._traer_datos('./api.php?controller=usuarios_controller&action=listar_usuarios')).usuarios;

        if (!usuarios) {
            this.#tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-danger">Error al cargar usuarios</td></tr>`;
            return;
        }else if (usuarios && usuarios.length === 0) {
            this.#tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-secondary">No hay usuarios registrados</td></tr>`;
            return;
        }


        document.getElementById('contador-usuarios').textContent = usuarios.length;
        
        this.#tbody.innerHTML = usuarios.map(u => `
            <tr data-id="${u.id}">
                <td class="ps-3 py-2 fw-semibold text-secondary align-middle">${u.id}</td>
                
                <td class="py-2 align-middle">
                    <input type="text" class="form-control form-control-sm border-0 bg-transparent px-2 shadow-none w-100 input-nombre" value="${u.nombre}" disabled>
                </td>
                
                <td class="py-2 align-middle">
                    <select class="form-select form-select-sm border-0 bg-transparent shadow-none w-100 select-rol" disabled>
                        <option value="Administrador" ${u.rol === 'Administrador' ? 'selected' : ''}>Administrador</option>
                        <option value="Secretaria" ${u.rol === 'Secretaria' ? 'selected' : ''}>Secretaria</option>
                        <option value="Usuario" ${u.rol === 'Usuario' ? 'selected' : ''}>Usuario</option>
                    </select>
                </td>
                
                <td class="py-2 align-middle">
                    <select class="form-select form-select-sm border-0 bg-transparent shadow-none w-100 select-estado fw-bold ${u.estado === 'Activo' ? 'text-success' : 'text-danger'}" disabled>
                        <option value="Activo" class="text-success fw-bold" ${u.estado === 'Activo' ? 'selected' : ''}>Activo</option>
                        <option value="Inactivo" class="text-danger fw-bold" ${u.estado === 'Inactivo' ? 'selected' : ''}>Inactivo</option>
                    </select>
                </td>
                
                <td class="text-end pe-3 py-2 align-middle">
                    <div class="btn-group btn-group-sm">
                        <button type="button" data-id="${u.id}" title="Editar" class="btn-editar-usuario btn btn-outline-primary py-0 px-2">
                            <i class="bi bi-pencil-fill fs-7"></i>
                        </button>
                        <button type="button" data-id="${u.id}" title="Eliminar" class="btn-eliminar-usuario btn btn-outline-danger py-0 px-2">
                            <i class="bi bi-trash-fill fs-7"></i>
                        </button>
                    </div>
                </td>
            </tr>`
        ).join('');

        document.querySelectorAll('.btn-eliminar-usuario').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                this.#eliminar_usuario(id);
            })
        });

        document.querySelectorAll('.btn-editar-usuario').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.#actualizar(e.currentTarget);
            });
        });
    }

    async #eliminar_usuario(id) {

        const {eliminado = null} = await this._enviar_datos('./api.php?controller=usuarios_controller&action=eliminar_usuario', {'id': id}); 
        if (eliminado) {
            this._notificacion('Usuario eliminado con exito', 'Exito');
            this.#cargar_usuarios();
            return;
        } 
    }

    async #crear_usuario(){

        if (!this._validar_formulario(this.#formulario_crear_usuario)) {
            this._notificacion('Debe llenar todos los campos');
            return;
        }

        const data = Object.fromEntries(new FormData(this.#formulario_crear_usuario));

        for (let clave in data) {
            if (data[clave].trim() === "") {
                this._notificacion("Rellene todos los campos");
                return;
            } else {
                data[clave] = data[clave].trim();
            }
        }

        if (data.pass !== data.confirmar_pass) {
            this._notificacion("Ha ingresado valores distintos en la contraseña");
            return;
        }

        const {creado = null} = await this._enviar_datos('./api.php?controller=usuarios_controller&action=crear_usuario', data);

        if (creado) {
            this._notificacion(`Se agrego al usuario ${data.nombre} al sistema`, 'Exito')
            this.#formulario_crear_usuario.reset();
            await this.#cargar_usuarios();
        } 
    }

    async #filtrar_usuarios() {
        const filtros =  Object.fromEntries(new FormData(this.#formulario_filtrar_usuarios));

        for (let clave in filtros) {
            if (filtros[clave].trim() === "") {
                filtros[clave] = null;
            } else {
                filtros[clave] = filtros[clave].trim();
            }
        }

        if(Object.values(filtros).every(v => v === null)) return;

        console.log('evento fiktrar')
        const {usuarios} = await this._enviar_datos('./api.php?controller=usuarios_controller&action=filtrar_usuarios', filtros)

        if(usuarios) {
            this.#cargar_usuarios(usuarios);
        }
    }

    async #actualizar(btn) {

        const tr = btn.closest('tr');
        const id = btn.getAttribute('data-id');
        const inputs = tr.querySelectorAll('.input-nombre, .select-rol, .select-estado');
        const isEditing = tr.classList.contains('table-active');

        if (!isEditing) {
            tr.classList.add('table-active'); 
            
            inputs.forEach(input => {
                input.removeAttribute('disabled');
                input.classList.remove('border-0', 'bg-transparent');
                input.classList.add('border', 'bg-white'); 
            });

            btn.classList.replace('btn-outline-primary', 'btn-success');
            btn.innerHTML = `<i class="bi bi-check-lg fs-7"></i>`;
            btn.title = "Guardar cambios";

        } else {
            const data = {
                id: id,
                nombre: tr.querySelector('.input-nombre').value,
                rol: tr.querySelector('.select-rol').value,
                estado: tr.querySelector('.select-estado').value
            };

            // limpieza
            for (let clave in data) {
                if (data[clave].trim() === "") {
                    this._notificacion("Rellene todos los campos");
                    return;
                } else {
                    data[clave] = data[clave].trim();
                }
            }

            btn.disabled = true;

            const {actualizado = null} = await this._enviar_datos('./api.php?controller=usuarios_controller&action=actualizar_usuario', data);

            if (actualizado) {
                
                tr.classList.remove('table-active');
                inputs.forEach(input => {
                    input.setAttribute('disabled', 'disabled');
                    input.classList.add('border-0', 'bg-transparent');
                    input.classList.remove('border', 'bg-white');
                });

                btn.classList.replace('btn-success', 'btn-outline-primary');
                btn.innerHTML = `<i class="bi bi-pencil-fill fs-7"></i>`;
                btn.title = "Editar";
            }

            btn.disabled = false;
        }
    }

    async #resetear() {
        await this.#cargar_usuarios();
        this.#formulario_filtrar_usuarios.reset();
    }
}


