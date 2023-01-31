<template>
<div class="modal" :class="(visible?'is-active':'')" >
  <div class="modal-background" @click="close()"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <div class="modal-card-title" v-html="title"></div>
      <button class="delete" aria-label="close" @click="close()"></button>
    </header>
    <section class="modal-card-body spaced" v-html="msg" />
    <footer class="modal-card-foot" style="justify-content: flex-end;">
      <button v-if="type=='alert'" class="button is-success" @click="ok()">{{ $ml.get('ok') }}</button>
      <button v-if="type=='confirm'" class="button is-success" @click="yes()">{{ $ml.get('yes') }}</button>
      <button v-if="type=='confirm'" class="button is-danger" @click="no()">{{ $ml.get('no') }}</button>
    </footer>
  </div>
</div>    
</template>
<script>
export default {
    data() {
        return {
            visible:false,
            msg:"",
            title:"",
            type:'alert'
        }
    },
    methods:{
        close() {
            this.visible = false;
        },
        open(type,title,msg) {
            this.msg = msg;
            this.type = type;
            this.title = title;
            this.visible = true;
        },
        alert(title,msg) {
            this.open('alert', title, msg)
        },
        confirm(title,msg) {
            this.open('confirm', title, msg)
        },
        ok() {
            this.$emit('answer', true);
            this.close();
        },
        yes() {
            this.$emit('answer', true);
            this.close();
        },
        no() {
            this.$emit('answer', false);
            this.close();
        }
    }
}
</script>
<style scoped>
.spaced {
    padding-top:40px;
    padding-bottom:50px
}
</style>