"use strict";(self["webpackChunkforesee"]=self["webpackChunkforesee"]||[]).push([[816],{90427:function(t,e,r){r.d(e,{Z:function(){return c}});r(36353);var i=function(){var t=this,e=t._self._c;return e("v-main",[e("v-navigation-drawer",{staticStyle:{height:"calc(100vh - 60px)"},attrs:{"mini-variant":t.MiniVariant,"expand-on-hover":!1,permanent:t.permanent,app:"",clipped:"",fixed:"",color:"white",width:"200px"},model:{value:t.drawer.visible,callback:function(e){t.$set(t.drawer,"visible",e)},expression:"drawer.visible"}},[e("v-list-item",[e("v-list-item-content",[e("v-row",{staticClass:"py-3",attrs:{"no-gutters":""}},[e("v-col",[e("v-icon",{attrs:{size:"24"},on:{click:function(e){t.MiniVariant=!t.MiniVariant}}},[t._v("mdi-menu")]),t.MiniVariant?t._e():e("div",{staticClass:"text-h5 grey--text font-weight-bold mb-1 mt-4"},[t._v(" 4C-Tech ")])],1)],1)],1)],1),e("v-list",{attrs:{nav:"",shaped:""}},[e("v-list-item-group",{attrs:{"active-class":"DrawerActive"},model:{value:t.currentRoute,callback:function(e){t.currentRoute=e},expression:"currentRoute"}},t._l(t.ComputeAccessMatrix,(function(r){return e("v-list-item",{key:r.text,on:{click:function(e){return t.$router.push("/"+r.route)}}},[e("v-list-item-action",[e("v-icon",[t._v(t._s(r.icon))])],1),e("v-list-item-content",[e("v-list-item-title",{staticClass:"text-subtitle-1"},[t._v(t._s(r.text))])],1),r.new?e("v-chip",{attrs:{color:"primary","x-small":""}},[t._v("new")]):t._e()],1)})),1)],1)],1)],1)},a=[],n={data(){return{currentRoute:this.$router.name,MiniVariant:!!this.$vuetify.breakpoint.smAndDown,permanent:!0}},watch:{"$vuetify.breakpoint.smAndDown"(t){t?(this.MiniVariant=!0,this.permanent=!0):this.MiniVariant=!1}}},s=n,o=r(61431),l=(0,o.Z)(s,i,a,!1,null,null,null),c=l.exports},21816:function(t,e,r){r.r(e),r.d(e,{default:function(){return m}});var i=function(){var t=this,e=t._self._c;return e("v-main",[e("v-container",{class:t.$vuetify.breakpoint.smAndUp&&"px-12 pt-6",attrs:{fluid:""}},[e("v-row",{attrs:{justify:"center"}},[e("v-col",{attrs:{cols:"12",sm:"12"}},[e("v-data-iterator",{attrs:{items:t.dataset,"items-per-page":t.itemsPerPage,page:t.page,search:t.search,loading:t.loading,"hide-default-footer":"","no-data-text":""},on:{"update:itemsPerPage":function(e){t.itemsPerPage=e},"update:items-per-page":function(e){t.itemsPerPage=e},"update:page":function(e){t.page=e}},scopedSlots:t._u([{key:"header",fn:function(){return[e("div",{staticClass:"mb-8"},[e("span",{staticClass:"text-subheading-1"},[t._v("Print range")]),e("v-row",{attrs:{"no-gutters":""}},[e("v-col",{attrs:{cols:"12",sm:"2"}},[e("v-text-field",{staticClass:"my-3 mr-6",attrs:{clearable:"",dense:"",outlined:"","hide-details":"","prepend-inner-icon":"mdi-barcode",label:"From"},model:{value:t.range.from,callback:function(e){t.$set(t.range,"from",e)},expression:"range.from"}})],1),e("v-col",{attrs:{cols:"12",sm:"2"}},[e("v-text-field",{staticClass:"my-3",attrs:{clearable:"",dense:"",outlined:"","hide-details":"","prepend-inner-icon":"mdi-barcode",label:"To"},model:{value:t.range.to,callback:function(e){t.$set(t.range,"to",e)},expression:"range.to"}})],1)],1),e("v-btn",{staticClass:"mt-3",attrs:{color:"primary",rounded:"",text:""},on:{click:t.GetBarcodes}},[e("v-icon",{attrs:{left:""}},[t._v("mdi-printer")]),t._v("GET BARCODES")],1),e("v-btn",{staticClass:"mt-3",attrs:{color:"primary",rounded:"",text:"",disabled:0===t.dataset.length},on:{click:t.Print}},[e("v-icon",{attrs:{left:""}},[t._v("mdi-printer")]),t._v("PRINT BARCODES")],1)],1)]},proxy:!0},{key:"loading",fn:function(){return[e("v-row",t._l(12,(function(t){return e("v-col",{key:t,attrs:{cols:"12",sm:"6",md:"4",lg:"3",xl:"2"}},[e("v-skeleton-loader",{attrs:{type:"table-cell@6"}})],1)})),1)]},proxy:!0},{key:"default",fn:function(r){return[e("section",{staticClass:"mt-5",attrs:{id:"barcodes"}},[e("table",{staticClass:"table table-bordered"},[e("tbody",t._l(r.items,(function(t,r){return e("tr",{key:r},[e("barcode",{attrs:{value:t.code.split("-")[0],text:t.code.split("-")[0],"text-margin":"5",width:1,height:"50","text-position":"top"}})],1)})),0)])])]}}])})],1)],1)],1),e("Drawer"),e("Alert",{ref:"Alert"})],1)},a=[],n=r(66523),s=r.n(n),o=r(90427),l={components:{barcode:s(),Drawer:o.Z},data(){return{loading:!1,headers:[],dataset:[],itemsPerPageArray:[12,24,36],search:"",SearchResults:[],filter:{},sortDesc:!1,page:1,itemsPerPage:5e3,sortBy:"no",range:{from:null,to:null}}},methods:{async Print(){await this.$htmlToPaper("barcodes")},GetBarcodes(){this.$Progress.start(),this.skeleton=!0,this.promiseFetch(this.$store.getters.fetchTimeout)(fetch(this.$store.getters.endpoint+"Locations/print.php",{method:"POST",headers:{Accept:"application/json",Authorization:`Bearer ${this.$store.getters.token}`,"Content-Type":"application/json"},body:JSON.stringify({storeId:this.$route.params.id,from:this.range.from,to:this.range.to})})).then((t=>{t.json().then((t=>{this.dataset=t.data,setTimeout((()=>{this.skeleton=!1}),1e3),this.NoContent.visible=!1}))})).catch((()=>{this.$refs.Alert.Alertify({visible:!0,content:this.$store.getters.fetchTimeoutError,title:"Connection Timeout",icon:"mdi-wifi-strength-1-alert",color:"error"})})).finally((()=>{this.$Progress.finish()}))}},computed:{filteredKeys(){return this.headers.filter((t=>"section"!==t||"store"!==t))}}},c=l,d=r(61431),u=(0,d.Z)(c,i,a,!1,null,null,null),m=u.exports}}]);