"use strict";(self["webpackChunkforesee"]=self["webpackChunkforesee"]||[]).push([[603],{90427:function(t,e,i){i.d(e,{Z:function(){return c}});i(36353);var r=function(){var t=this,e=t._self._c;return e("v-main",[e("v-navigation-drawer",{staticStyle:{height:"calc(100vh - 60px)"},attrs:{"mini-variant":t.MiniVariant,"expand-on-hover":!1,permanent:t.permanent,app:"",clipped:"",fixed:"",color:"white",width:"200px"},model:{value:t.drawer.visible,callback:function(e){t.$set(t.drawer,"visible",e)},expression:"drawer.visible"}},[e("v-list-item",[e("v-list-item-content",[e("v-row",{staticClass:"py-3",attrs:{"no-gutters":""}},[e("v-col",[e("v-icon",{attrs:{size:"24"},on:{click:function(e){t.MiniVariant=!t.MiniVariant}}},[t._v("mdi-menu")]),t.MiniVariant?t._e():e("div",{staticClass:"text-h5 grey--text font-weight-bold mb-1 mt-4"},[t._v(" 4C-Tech ")])],1)],1)],1)],1),e("v-list",{attrs:{nav:"",shaped:""}},[e("v-list-item-group",{attrs:{"active-class":"DrawerActive"},model:{value:t.currentRoute,callback:function(e){t.currentRoute=e},expression:"currentRoute"}},t._l(t.ComputeAccessMatrix,(function(i){return e("v-list-item",{key:i.text,on:{click:function(e){return t.$router.push("/"+i.route)}}},[e("v-list-item-action",[e("v-icon",[t._v(t._s(i.icon))])],1),e("v-list-item-content",[e("v-list-item-title",{staticClass:"text-subtitle-1"},[t._v(t._s(i.text))])],1),i.new?e("v-chip",{attrs:{color:"primary","x-small":""}},[t._v("new")]):t._e()],1)})),1)],1)],1)],1)},n=[],a={data(){return{currentRoute:this.$router.name,MiniVariant:!!this.$vuetify.breakpoint.smAndDown,permanent:!0}},watch:{"$vuetify.breakpoint.smAndDown"(t){t?(this.MiniVariant=!0,this.permanent=!0):this.MiniVariant=!1}}},s=a,o=i(61431),l=(0,o.Z)(s,r,n,!1,null,null,null),c=l.exports},14603:function(t,e,i){i.r(e),i.d(e,{default:function(){return h}});var r=function(){var t=this,e=t._self._c;return e("v-main",[e("v-container",{attrs:{fluid:""}},[e("v-row",{staticStyle:{height:"100vh"},attrs:{justify:"center",align:"center"}},[e("barcode",{attrs:{value:24537892844,text:"Master reset Barcode","text-margin":"5",width:1,height:"50","text-position":"top"}})],1)],1),e("Drawer"),e("Alert",{ref:"Alert"})],1)},n=[],a=i(66523),s=i.n(a),o=i(90427),l={components:{barcode:s(),Drawer:o.Z},data(){return{loading:!1,headers:[],dataset:[],itemsPerPageArray:[12,24,36],search:"",SearchResults:[],filter:{},sortDesc:!1,page:1,itemsPerPage:5e3,sortBy:"no",range:{from:null,to:null}}},methods:{async Print(){await this.$htmlToPaper("barcodes")}}},c=l,u=i(61431),v=(0,u.Z)(c,r,n,!1,null,null,null),h=v.exports}}]);