<template>
    <div>
    <h1 class="title">{{ $ml.get('admin')}} - {{ $ml.get('message') }}</h1>
        <div class="field">
            <label class="label">{{ $ml.get("title") }}</label>
            <div class="control">
                <input class="input" type="text" v-model="status.title" style="width:500px" maxlength="128">
            </div>
        </div>
        <div class="field">
            <label class="label">{{ $ml.get("message") }}</label>
            <div class="control">
                <textarea class="textarea" v-model="status.msg" style="width:500px" maxlength="512"></textarea>
            </div>
        </div>
        <div class="columns">
            <div class="column is-half">
                
            </div>
            <div class="column is-one-quarter">
                <a class="button is-primary" @click="save()">{{ $ml.get('save') }}</a>&nbsp;
            </div>
            <div class="column is-one-quarter">
                <a class="button is-danger"  @click="del()">{{ $ml.get('delete') }}</a>
            </div>
        </div>            
        <br/>

    </div>    
</template>
<script>
import axios from "axios";
import { mapGetters } from "vuex";
axios.defaults.withCredentials = true;
export default {
    data() {
        return {
            status:{title:"",msg:""}
        }
    },
    methods: {
        get() {
            axios
                .get(this.getApiStatusUrl)
                .then(res => {
                    if (res.data.length > 0 ) this.status = res.data[0];
                });
                //.catch(error => console.log(error));
        },
        save() {
            axios
                .post(this.getApiAdminUrl + "/status",this.status)
                .then( res => {
                    if (res.data.length > 0 ) this.status = res.data[0];
                    this.$router.push('/search');
                });
                //.catch(error => console.log(error));
        },
        del() {
            axios
                .delete(this.getApiAdminUrl + "/status")
                .then(res => {
                    if (res.data.length > 0 ) this.status = res.data[0];
                    this.status = {title:"",msg:""};
                    this.$router.push('/search');
                });
                //.catch(error => console.log(error));
        }
    },
    computed: mapGetters(['getApiAdminUrl','getApiStatusUrl']),
    created() {
        this.get();
    }
}
</script>
<style scoped>

</style>