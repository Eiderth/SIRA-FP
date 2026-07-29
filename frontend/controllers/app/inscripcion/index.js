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
         direccion_representante: {},
      },
      representante_secundario: {
         persona: {},
         persona_representante: {},
         direccion_representante: {},
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

      this.#seccion_1_controller.init(this._data);

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
      // if(!this._validar_formulario(document.querySelectorAll('form'))) {
      //    return;
      // }

      if (this.#cont == 1) {

         this._data.inscripcion = Object.fromEntries(new FormData(document.getElementById('formulario-inscripcion')));
         this._data.estudiante.persona = Object.fromEntries(new FormData(document.getElementById('formulario-persona')));
         this._data.estudiante.persona_estudiante = Object.fromEntries(new FormData(document.getElementById('formulario-persona-estudiante')));
         
         this.#btn_atras.classList.remove('d-none'); 
         console.log(this._data)
      } 

      if (this.#cont == 2) {
         this._data.estudiante.antropometrico = Object.fromEntries(new FormData(document.getElementById('formulario-antropometrico')));
         this._data.estudiante.salud = Object.fromEntries(new FormData(document.getElementById('formulario-salud')));
         this._data.estudiante.extra_curricular = Object.fromEntries(new FormData(document.getElementById('formulario-extra-curricular')));
         console.log(this._data)
      } 

      if (this.#cont == 3) {
         this._data.representante_principal = Object.fromEntries(new FormData(document.getElementById('formulario-representante-principal')));
         this._data.direccion_r_principal = Object.fromEntries(new FormData(document.getElementById('formulario-direccion-r-principal')));
         if(this._data.direccion_r_principal.parroquia_id){
            this._data.direccion_r_principal.ciudad_id = null;
         }
      }

      if (this.#cont == 4) {
         this._data.representante_secundario = Object.fromEntries(new FormData(document.getElementById('formulario-representante-secundario')));
         this._data.direccion_r_secundario = Object.fromEntries(new FormData(document.getElementById('formulario-direccion-r-secundario')));
         if(this._data.direccion_r_principal.parroquia_id){
            this._data.direccion_r_principal.ciudad_id = null;
         }
      }

      this.#cont ++;

      if(this.#cont != 5) {

         if(this.#cont == 2) this.#seccion_2_controller.init({...this._data.antropometricos, ...this._data.salud, ...this._data.extra_curriculares});
         if(this.#cont == 3) this.#seccion_3_controller.init({...this._data.representante_principal, ...this._data.direccion_r_principal});
         if(this.#cont == 4) {
            this.#seccion_4_controller.init({...this._data.representante_secundario, ...this._data.direccion_r_secundario});
            this.#btn_siguiente.textContent = 'Inscribir';
         }

      } else {

         for (let clave in this._data) { 
            this._limpiar_objeto(this._data[clave]);
         }
         
         const resp = await this._enviar_datos('./api.php?controller=inscripcion_controller&action=guardar_estudiante', this._data);
         console.log(resp)
         
         if(resp.estado !== 'error' ) {
            this.#seccion_exito.init(
               {data_estudiante: this._data.estudiante, numero_inscripcion: resp.llave_inscripcion, cedula_escolar: resp.cedula_escolar},
               () => this.#seccion_1_controller.init(this._data)
            );

            Object.assign(this._data, {
               periodo: {}, inscripcion: {}, estudiante: {}, antropometricos: {},
               salud : {}, extra_curriculares: {}, representante_principal: {},
               direccion_r_principal: {}, representante_secundario: {}, direccion_r_secundario: {},
            });

            this.#cont = 1;
            this.#btn_siguiente.textContent = 'Siguiente';

         } else {
            
            this.#seccion_error.init(resp.mensaje, () => this.#seccion_4_controller.init({...this._data.representante_secundario, ...this._data.direccion_r_secundario}));
            this.#cont --;
         }

      } 
   }

   #reset() {
      if (this.#cont == 1) {   
         Object.assign(this._data, { 
            periodo: {}, inscripcion: {}, estudiante: {}, antropometricos: {},
            salud : {}, extra_curriculares: {}, representante_principal: {},
            direccion_r_principal: {}, representante_secundario: {}, direccion_r_secundario: {},
         });
      } 

      document.getElementById('select-estado')?.parentElement.classList.remove('d-none');
      document.getElementById('select-municipio')?.parentElement.classList.remove('d-none');
      document.getElementById('select-ciudad')?.parentElement.classList.remove('d-none');
      document.querySelectorAll('form').forEach(form => form.reset());
   }

   async #atras() {
      if (this.#cont == 2) {
         this._data.antropometricos = Object.fromEntries(new FormData(document.getElementById('formulario-antropometricos')));
         this._data.salud = Object.fromEntries(new FormData(document.getElementById('formulario-salud')));
         this._data.extra_curriculares = Object.fromEntries(new FormData(document.getElementById('formulario-extra-curriculares')));
      } 

      if (this.#cont == 3) {
         this._data.representante_principal = Object.fromEntries(new FormData(document.getElementById('formulario-representante-principal')));
         this._data.direccion_r_principal = Object.fromEntries(new FormData(document.getElementById('formulario-direccion-r-principal')));
      }

      if (this.#cont == 4) {
         this._data.representante_secundario = Object.fromEntries(new FormData(document.getElementById('formulario-representante-secundario')));
         this._data.direccion_r_secundario = Object.fromEntries(new FormData(document.getElementById('formulario-direccion-r-secundario')));
      }

      this.#cont --;

      if(this.#cont === 1) {
         this.#btn_atras.classList.add('d-none');
         this.#seccion_1_controller.init({...this._data.estudiante,...this._data.inscripcion, ...this._data.periodo});
      }

      if (this.#cont == 2){
         this.#seccion_2_controller.init({...this._data.antropometricos, ...this._data.salud, ...this._data.extra_curriculares});
      }

      if (this.#cont == 3){
         this.#seccion_3_controller.init({...this._data.representante_principal, ...this._data.direccion_r_principal});
         this.#btn_siguiente.textContent = 'Siguiente';
      }
   }

   _alterar_data(data){
      Object.assign(this._data, data);
   }
}



