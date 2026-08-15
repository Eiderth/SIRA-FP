import Utils from "../../../core/utils.js";
import Seccion_1_controller from "./secciones/seccion_1.js";
import Seccion_2_controller from "./secciones/seccion_2.js";
import Seccion_3_controller from "./secciones/seccion_3.js";
import Seccion_4_controller from "./secciones/seccion_4.js";
import Seccion_exito from "./interfaz-final/seccion_exito.js";
import Seccion_error from "./interfaz-final/seccion_error.js";


export default class Inscripcion_controller extends Utils {

   _data = {
      inscripcion: {},
      estudiante: {
         persona: {},
         persona_estudiante: {},
         antropometrico: {},
         salud : {},
         extra_curricular: {},
      },
      representante_principal: {
         persona: {},
         persona_representante: {},
         direccion: {},
      },
      representante_secundario: {
         persona: {},
         persona_representante: {},
         direccion: {},
      },
   }

   #parametros_formulario; 

   #seccion_1_controller;
   #seccion_2_controller;
   #seccion_3_controller;
   #seccion_4_controller;
   #seccion_exito;
   #seccion_error;

   #cont; 
   
   #html
   #btn_siguiente;
   #btn_atras;
   #btn_reset; 

   constructor() {
      super(); 
      this.#html = this._traer_html('./frontend/views/app/formulario-inscripcion/index.html');
   }

   async init() {

      const html = await this.#html;
      
      await this._inyectar_html(document.getElementById('root-app'), html);
      this.#parametros_formulario = await this.#traer_parametros();
      
      this.#seccion_1_controller = new Seccion_1_controller(this.#parametros_formulario,(data) => this._alterar_data(data));
      this.#seccion_2_controller = new Seccion_2_controller();
      this.#seccion_3_controller = new Seccion_3_controller(this.#parametros_formulario);
      this.#seccion_4_controller = new Seccion_4_controller(this.#parametros_formulario);
      this.#seccion_exito = new Seccion_exito();
      this.#seccion_error = new Seccion_error();

      this.#seccion_1_controller.init({...this._data.estudiante.persona,...this._data.estudiante.persona_estudiante, ...this._data.inscripcion});

      this.#cont = 1;
      this.#btn_siguiente = document.getElementById('btn-siguiente');
      this.#btn_atras = document.getElementById('btn-atras');
      this.#btn_reset = document.getElementById('btn-reset');

      this.#btn_siguiente.addEventListener('click', () => this.#siguiente());
      this.#btn_reset.addEventListener('click', () => this.#reset());
      this.#btn_atras.addEventListener('click', () => this.#atras());
   }

   async #traer_parametros() {
      const json = localStorage.getItem('parametros_formulario');
      if(json != null) {
         return JSON.parse(json);
      } else {
         const resp = await this._traer_datos('./api.php?controller=inscripcion_controller&action=traer_parametros');

         if (resp.estado !== 'completado') {
            this._notificacion('Error al conectar con la Base de datos');
            return;
         }

         localStorage.setItem('parametros_formulario', JSON.stringify(resp.parametros));     
         return resp.parametros;
      }
   }

   async #siguiente() {
      if(!this._validar_formulario(document.querySelectorAll('form'))) {
         return;
      }

      if (this.#cont == 1) {

         this._data.inscripcion = Object.fromEntries(new FormData(document.getElementById('formulario-inscripcion')));
         this._data.estudiante.persona = Object.fromEntries(new FormData(document.getElementById('formulario-persona')));
         this._data.estudiante.persona_estudiante = Object.fromEntries(new FormData(document.getElementById('formulario-persona-estudiante')));
         
         this._data.inscripcion.grado_seccion_id = this.#parametros_formulario.grados_secciones.find(gs => gs.grado_id == this._data.inscripcion.grado_id && gs.seccion_id == this._data.inscripcion.seccion_id).id;

         this.#btn_atras.classList.remove('d-none'); 
      } 

      if (this.#cont == 2) {
         this._data.estudiante.antropometrico = Object.fromEntries(new FormData(document.getElementById('formulario-antropometrico')));
         this._data.estudiante.salud = Object.fromEntries(new FormData(document.getElementById('formulario-salud')));
         this._data.estudiante.extra_curricular = Object.fromEntries(new FormData(document.getElementById('formulario-extra-curricular')));
      } 

      if (this.#cont == 3) {
         this._data.representante_principal.persona = Object.fromEntries(new FormData(document.getElementById('formulario-persona')));
         this._data.representante_principal.persona_representante = Object.fromEntries(new FormData(document.getElementById('formulario-persona-representante')));         
         this._data.representante_principal.direccion = Object.fromEntries(new FormData(document.getElementById('formulario-direccion')));
      }

      if (this.#cont == 4) {
         this._data.representante_secundario.persona = Object.fromEntries(new FormData(document.getElementById('formulario-persona')));
         this._data.representante_secundario.persona_representante = Object.fromEntries(new FormData(document.getElementById('formulario-persona-representante')));         
         this._data.representante_secundario.direccion = Object.fromEntries(new FormData(document.getElementById('formulario-direccion')));
      }

      this.#cont ++;

      if(this.#cont != 5) {

         if(this.#cont == 2) this.#seccion_2_controller.init({...this._data.estudiante.antropometrico, ...this._data.estudiante.salud, ...this._data.estudiante.extra_curricular});
         if(this.#cont == 3) this.#seccion_3_controller.init({...this._data.representante_principal.persona, ...this._data.representante_principal.persona_representante, ...this._data.representante_principal.direccion});
         if(this.#cont == 4) {
            this.#seccion_4_controller.init({...this._data.representante_secundario.persona, ...this._data.representante_secundario.persona_representante, ...this._data.representante_secundario.direccion});
            this.#btn_siguiente.textContent = 'Inscribir';
         }

      } else {

         delete this._data.estudiante.persona_estudiante.estado_nacimiento_id;
         delete this._data.estudiante.persona_estudiante.municipio_nacimiento_id;
         
         delete this._data.inscripcion.nivel_academico_id;
         delete this._data.inscripcion.grado_id;
         delete this._data.inscripcion.seccion_id;

         delete this._data.representante_principal.direccion.estado_id;
         delete this._data.representante_secundario.direccion.estado_id;
         delete this._data.representante_principal.direccion.municipio_id;
         delete this._data.representante_secundario.direccion.municipio_id;

         // === Limpieza ===
         this._limpiar_objeto(this._data.inscripcion);
         
         for (let clave in this._data.estudiante) { 
          
            this._limpiar_objeto(this._data.estudiante[clave]);
            
         }

         for (let clave in this._data.representante_principal) { 
          
            this._limpiar_objeto(this._data.representante_principal[clave]);

         }

         for (let clave in this._data.representante_secundario) { 
          
            this._limpiar_objeto(this._data.representante_secundario[clave]);
            
         }


         const resp = await this._enviar_datos('./api.php?controller=inscripcion_controller&action=guardar_estudiante', this._data);

         if(resp.estado !== 'error' ) {
            this.#seccion_exito.init(
               this._data.estudiante, 
               resp.llave_inscripcion, 
               resp.cedula_escolar,
               () => {
                  this.#seccion_1_controller.init({...this._data.estudiante.persona, ...this._data.estudiante.persona_estudiante, ...this._data.inscripcion});
                  this.#cont = 1;
                  this.#btn_siguiente.textContent = 'Siguiente';
                  this.#reset();
               }  
            );

         } else {
            
            this.#seccion_error.init(resp.mensaje, () => this.#seccion_4_controller.init({...this._data.representante_secundario.persona, ...this._data.representante_secundario.persona_representante, ...this._data.representante_secundario.direccion}));
            this.#cont --;
         }

      } 
   }

   #reset() {
      if (this.#cont == 1) {   
         Object.assign(this._data, {
               inscripcion: {},
               estudiante: {
                  persona: {},
                  persona_estudiante: {},
                  antropometrico: {},
                  salud : {},
                  extra_curricular: {},
               },
               representante_principal: {
                  persona: {},
                  persona_representante: {},
                  direccion: {},
               },
               representante_secundario: {
                  persona: {},
                  persona_representante: {},
                  direccion: {},
               },
            }
         );
      } 

      document.querySelectorAll('form').forEach(form => form.reset());
      if(this.#cont == 1) this.#seccion_1_controller.init({...this._data.estudiante.persona, ...this._data.estudiante.persona_estudiante, ...this._data.inscripcion});
      if(this.#cont == 3) this.#seccion_3_controller.init({...this._data.representante_principal.persona, ...this._data.representante_principal.persona_representante, ...this._data.representante_principal.direccion});
      if(this.#cont == 4) {
         this.#seccion_4_controller.init({...this._data.representante_secundario.persona, ...this._data.representante_secundario.persona_representante, ...this._data.representante_secundario.direccion});
         this.#btn_siguiente.textContent = 'Inscribir';
      }

   }

   async #atras() {
      if (this.#cont == 2) {
         this._data.estudiante.antropometrico = Object.fromEntries(new FormData(document.getElementById('formulario-antropometrico')));
         this._data.estudiante.salud = Object.fromEntries(new FormData(document.getElementById('formulario-salud')));
         this._data.estudiante.extra_curricular = Object.fromEntries(new FormData(document.getElementById('formulario-extra-curricular')));
      } 

      if (this.#cont == 3) {
         this._data.representante_principal.persona = Object.fromEntries(new FormData(document.getElementById('formulario-persona')));
         this._data.representante_principal.persona_representante = Object.fromEntries(new FormData(document.getElementById('formulario-persona-representante')));
         this._data.representante_principal.direccion = Object.fromEntries(new FormData(document.getElementById('formulario-direccion')));
      
      }

      if (this.#cont == 4) {
         this._data.representante_secundario.persona = Object.fromEntries(new FormData(document.getElementById('formulario-persona')));
         this._data.representante_secundario.persona_representante = Object.fromEntries(new FormData(document.getElementById('formulario-persona-representante')));
         this._data.representante_secundario.direccion = Object.fromEntries(new FormData(document.getElementById('formulario-direccion')));
      }

      this.#cont --;

      if(this.#cont === 1) {
         this.#btn_atras.classList.add('d-none');
         this.#seccion_1_controller.init({...this._data.estudiante.persona,...this._data.estudiante.persona_estudiante ,...this._data.inscripcion});
      }

      if (this.#cont == 2){
         this.#seccion_2_controller.init({...this._data.estudiante.antropometrico, ...this._data.estudiante.salud, ...this._data.estudiante.extra_curricular});
      }

      if (this.#cont == 3){
         this.#seccion_3_controller.init({...this._data.representante_principal.persona, ...this._data.representante_principal.persona_representante, ...this._data.representante_principal.direccion});
         this.#btn_siguiente.textContent = 'Siguiente';
      }

      if (this.#cont == 4){
         this.#seccion_4_controller.init({...this._data.representante_secundario.persona, ...this._data.representante_secundario.persona_representante, ...this._data.representante_secundario.direccion});
         this.#btn_siguiente.textContent = 'Inscribir';
      }
   }

   _alterar_data(data){
      Object.assign(this._data, data);
   }
}



