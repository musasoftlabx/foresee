"use strict";(self["webpackChunkforesee"]=self["webpackChunkforesee"]||[]).push([[651],{46651:function(e,t,i){i.r(t),i.d(t,{default:function(){return d}});var s=function(){var e=this,t=e._self._c;return t("section",{directives:[{name:"resize",rawName:"v-resize",value:e.OnResize,expression:"OnResize"}]},[t("v-app-bar",{staticClass:"elevation-0 px-3",attrs:{app:"",fixed:"",dark:"",color:"transparent"}},[t("div",{staticClass:"text-h4 grey--text font-weight-bold mb-1 mt-4"},[e._v("4C-Tech")]),t("v-spacer"),t("v-btn",{staticClass:"mt-3",attrs:{color:"green",text:""},on:{click:e.DownloadAPK}},[t("v-icon",{attrs:{left:""}},[e._v("mdi-android")]),e._v("DOWNLOAD APP")],1)],1),t("v-container",[t("v-row",{staticClass:"mt-n16",class:e.$vuetify.breakpoint.smAndUp&&"mx-3",staticStyle:{height:"100vh"},attrs:{align:"center"}},[t("v-col",{attrs:{cols:"12",md:"5"}},[t("v-card",{attrs:{"max-width":"600",flat:"",color:"transparent"}},[t("v-row",[e.WindowSize.y>700?t("v-col",{staticClass:"text-center"},[t("img",{staticClass:"mt-n5",attrs:{width:"200",src:`${e.$store.getters.server}img/login.png`}}),t("div",{staticClass:"text-h5 mt-n6 mb-5"},[e._v("Let's get you signed in")])]):e._e()],1),t("v-form",{ref:"Login",staticClass:"px-5",model:{value:e.login.valid,callback:function(t){e.$set(e.login,"valid",t)},expression:"login.valid"}},[t("v-select",{attrs:{rules:[e.validators.required],items:e.login.domains,label:"Domain *",filled:"",rounded:"","prepend-inner-icon":"mdi-domain"},model:{value:e.login.domain,callback:function(t){e.$set(e.login,"domain",t)},expression:"login.domain"}}),t("v-text-field",{ref:"LoginUsername",attrs:{rules:[e.validators.required],name:"username",label:"Username *",filled:"",rounded:"",clearable:"",autofocus:"","prepend-inner-icon":"mdi-account-circle"},on:{keyup:function(t){return!t.type.indexOf("key")&&e._k(t.keyCode,"enter",13,t.key,"Enter")?null:e.Login.apply(null,arguments)}},model:{value:e.login.username,callback:function(t){e.$set(e.login,"username",t)},expression:"login.username"}}),"Scanner"!==e.login.domain?t("v-text-field",{attrs:{rules:[e.validators.required],name:"password",label:"Password *",filled:"",rounded:"",clearable:"",counter:"","prepend-inner-icon":"mdi-shield-key","append-icon":e.textpass?"mdi-eye":"mdi-eye-off",type:e.textpass?"text":"password"},on:{"click:append":e.TextPass,keyup:function(t){return!t.type.indexOf("key")&&e._k(t.keyCode,"enter",13,t.key,"Enter")?null:e.Login.apply(null,arguments)}},model:{value:e.login.password,callback:function(t){e.$set(e.login,"password",t)},expression:"login.password"}}):e._e(),t("div",{staticClass:"mb-3 d-flex justify-center"},[t("v-btn",{staticClass:"GradientButton",attrs:{color:"primary",width:"200",rounded:"",large:"",dark:!e.login.valid||e.login.loading,disabled:!e.login.valid||e.login.loading,loading:e.login.loading},on:{click:e.Login}},[e._v("LOGIN")])],1)],1)],1)],1),t("v-col",{staticClass:"hidden-sm-and-down",attrs:{cols:"12",md:"7",align:"end"}},[t("img",{staticClass:"mt-n8",attrs:{width:"90%",src:`${e.$store.getters.server}img/management.png`}}),t("div",{staticClass:"text-h3 gradient-text"},[e._v("Foresee Technologies")]),t("div",{staticClass:"teal--text text-h6 my-3"},[e._v("Inventory Management System")])])],1)],1),t("div",{attrs:{id:"LoginSVG"}},[t("svg",{attrs:{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 1000 125",preserveAspectRatio:"none"}},[t("defs",[t("linearGradient",{attrs:{id:"LoginSVGGradient"}},[t("stop",{attrs:{offset:"0%","stop-color":"#3f4c6b"}}),t("stop",{attrs:{offset:"80%","stop-color":"#859398"}})],1)],1),t("path",{staticClass:"divider-fill",staticStyle:{opacity:"0.3"},attrs:{d:"m 0,81.659348 c 81.99918,0 88.739113,19.697032 141.99858,19.697032 46.67953,0 33.12967,-19.551122 85.12915,-19.551122 57.49943,0 75.87924,34.141512 143.87856,34.141512 55.99944,0 98.99901,-36.475982 149.49851,-36.475982 76.49923,0 77.32923,29.482322 131.49868,43.284832 55.99944,14.2694 72.82927,-44.169982 142.82857,-44.169982 41.37959,0 74.35926,28.033012 103.68897,28.033012 C 942.67057,106.61865 1000,75.093678 1000,75.093678 V 24.999998 H 0 Z"}}),t("path",{staticClass:"divider-fill",attrs:{d:"m 0,81.988666 c 97.331947,-14.74806 115.33231,19.667594 176.67353,19.667594 53.33107,0 60.14121,-51.965834 122.64246,-51.965834 84.00168,0 116.35232,90.668944 200.004,73.034484 80.0016,-16.85492 86.00172,-63.205964 153.34306,-63.205964 49.661,0 86.65174,33.00405 124.00248,33.00405 33.33067,0 51.27103,-31.16054 78.00156,-31.16054 43.93088,0 51.28103,31.0868 97.90196,28.83245 C 976.99954,89.015066 1000,74.267006 1000,74.267006 V 5.8499091e-6 H 0 Z"}})])]),t("Alert",{ref:"Alert"})],1)},n=[],o=(i(2892),i(36353),{data(){return{WindowSize:{x:0,y:0},login:{visible:!0,valid:!1,loading:!1,domain:null,domains:[],username:null,password:null}}},beforeCreate(){clearInterval(window.tokenify),localStorage.removeItem("_4c_token_")},created(){this.Domains()},mounted(){this.OnResize(),this.$refs.LoginUsername.$el.focus()},methods:{OnResize(){this.WindowSize={x:window.innerWidth,y:window.innerHeight}},Domains(){this.$Progress.start(),this.promiseFetch(this.$store.getters.fetchTimeout)(fetch(`${this.$store.getters.endpoint}Login/Domains/`)).then((e=>e.json().then((t=>{e.status>200?this.$Progress.fail():(this.$Progress.finish(),this.login.domains=t,this.login.domain=t[0],localStorage.domains=JSON.stringify(t))})))).catch((()=>{this.$Progress.fail(),this.$refs.Alert.Alertify({visible:!0,content:this.$store.getters.fetchTimeoutError,title:"Connection Timeout",icon:"mdi-wifi-strength-1-alert",color:"error"})}))},Login(){this.login.loading=!0,this.promiseFetch(this.$store.getters.fetchTimeout)(fetch(`${this.$store.getters.endpoint}Login/`,{method:"POST",body:JSON.stringify({domain:this.login.domain,username:this.login.username,password:btoa(this.login.password)})})).then((e=>e.json().then((t=>{e.status>200?this.$refs.Alert.Alertify({visible:!0,content:t.content,title:t.title,icon:"mdi-shield-account",color:"info"}):(localStorage.setItem("_4c_token_",t.token),this.$store.dispatch("StoreToken",t.token),localStorage.lastRoute?this.$router.push(localStorage.lastRoute):this.$router.push("/"))})))).catch((()=>this.$refs.Alert.Alertify({visible:!0,content:this.$store.getters.fetchTimeoutError,title:"Connection Timeout",icon:"mdi-wifi-strength-1-alert",color:"error"}))).finally((()=>this.login.loading=!1))},DownloadAPK(){location.href=`${this.$store.getters.endpoint}DownloadAPK/`}}}),r=o,a=i(61431),l=(0,a.Z)(r,s,n,!1,null,null,null),d=l.exports}}]);