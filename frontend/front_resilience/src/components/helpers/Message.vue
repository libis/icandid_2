<template>
    <article class="message is-warning" v-if="visible">
        <div class="message-header">
            <p  v-html="status[0].title"></p>
            <button class="delete" aria-label="delete" @click="hide()"></button>
        </div>
        <div class="message-body" v-html="message"></div>
    </article> 
</template>
<script>
import axios from "axios";

import { mapGetters } from "vuex";
export default {
    data() {
        return {
            status:[],
            hidden: false
        }
    },
    computed: {
    ...mapGetters(["getApiStatusUrl"]),   
        visible() {
            return (this.status.length > 0 && !this.hidden)
        },
        message() {
            return this.status[0].msg.replace(/\n/g, "<br />")
        }
    },
    created() {
        this.getStatus();
    },
    methods:{
        hide() {
            this.hidden=true
            this.$cookies.set('message_hidden',this.hidden,12*60*60)
        },
        getStatus() {
            this.hidden = this.$cookies.get("message_hidden");
            axios
                .get(this.getApiStatusUrl)
                .then(res => {
                    this.status = res.data;
                });
            //.catch(error => console.log(error));
        }
    },
    watch:{
        $route (to, from){
            if (to.name != "" && from.name=="Status") {
                this.getStatus();
            }
        }
    }

}
</script>
<style scoped>

</style>
