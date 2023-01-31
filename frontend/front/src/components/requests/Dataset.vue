<template>
    <div class="box">
        <div :class="step==1 ? '' : 'hidden'"> 
            <div class="field">
                <label class="label">{{ $ml.get('namefirstname') }}</label>
               {{ formdata.name }}         
            </div>
            <div class="field">
                <label class="label">{{ $ml.get('email') }}</label>
               {{ formdata.email }}         
            </div>
            <div class="field">
                <label class="label">{{ $ml.get('datasets') }}</label>
                {{ datasets }}
            </div>
            <div class="field">
                <label class="label">{{ $ml.get('requestreason') }}</label>
                <textarea class="textarea" v-model="formdata.reason"></textarea>
                <p class="help is-danger is-hidden" ref="reason">{{ $ml.get('requestreason_warn') }}</p>
            </div>
            <div class="columns">
                <div class="column is-half"></div>
                <div class="column is-half">
                    <a class="button is-primary" @click="verzenden()">
                    {{ $ml.get('sendrequest') }}
                    </a>
                </div>
            </div> 
        </div>
        <div :class="step==2 ? '' : 'hidden'"> 
            <div class="field" v-html="$ml.get('requestanswerpos')"></div>
        </div>

        <div :class="step==3 ? '' : 'hidden'"> 
            <div class="field" v-html="$ml.get('requestanswerneg')">
                
            </div>
        </div>
    </div>
</template>
<script>
import axios from "axios";
import { mapGetters } from "vuex";
axios.defaults.withCredentials = true;
export default {
    data() {
        return {
            formdata:{
                name:"",
                email:"",
                datasets:[],
                reason:""
            },
            step:1
        }
    },
    components:{
    },
    computed: { ...mapGetters(['getApiFormUrl','getUser','getUsername','getSelectedDatasets']), 
        datasets() {
            return this.getSelectedDatasets.map(el => el.name).join(", ");
        }
    },
    methods: { 
        verzenden() {
            var error = 0;
            if (this.formdata.reason.trim() == "") {
                this.$refs.reason.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.reason.classList.add('is-hidden')
            } 
            if (error > 0) return false;   
            this.formdata.datasets = this.getSelectedDatasets.map(el => el.name + ' (' + el.internalident + ')');                   
            var data = {"subject":"Aanvraag toegang dataset","formdata":this.formdata}
            axios
                .post(this.getApiFormUrl, data)
                .then(
                    this.step = 2
                )
                .catch(error => {
                    console.log(error)
                    this.step = 3
                });
        }
    },
    created() {
        this.formdata.name = this.getUsername;
        this.formdata.email = this.getUser.email;
    },
    watch: {
        getSelectedDatasets: {
            deep: true,
            handler() {
                this.step = 1;
                this.formdata.reason = "";
            }
        }

    }
}    
</script>
<style scoped>
input[type=text] {
    width:400px
}
input[type=email] {
    width:400px
}
input[type=radio] {
    margin-left:10px
}
.field {
    margin-bottom:20px
}
.hidden {
    display:none;
}
</style>