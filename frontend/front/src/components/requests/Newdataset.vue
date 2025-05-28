<template>
    <div class="box">
        <div :class="step==1 ? '' : 'hidden'"> 
            <table border="0" cellspacing="3" cellpadding="0" width="300px">
                <tr>
                    <td width="30%">
                        <label class="label">{{ $ml.get('namefirstname') }}</label>
                    </td>
                    <td>
                        {{  this.getUsername }}
                    </td>
                </tr>
                <tr>
                    <td width="30%">
                        <label class="label">{{ $ml.get('email') }}</label>
                    </td>
                    <td>
                        {{  this.getUser.email }}
                    </td>
                </tr>
                <tr>
                    <td width="30%">
                        <label class="label">{{ $ml.get('title') }}</label>
                    </td>
                    <td>
                        <div class="control">
                            <input class="input" type="text" placeholder="" v-model="formdata.title">
                        </div>
                        <p class="help is-danger is-hidden" ref="title">{{ $ml.get('title_warn') }}</p>
                    </td>
                </tr>
                <tr>
                    <td width="30%">
                        <label class="label">{{ $ml.get('provider') }}</label>
                    </td>
                    <td>
                        <div class="control">
                        <input class="input" style="width:200%" list="providers" name="provider" ref="provider" v-model="formdata.provider">
                            <datalist id="providers">
                                <option v-for="(option, key) in this.providers" :key="key" v-bind:value="option"></option>
                            </datalist>                
                        </div>
                        <p class="help is-danger is-hidden" ref="provider">{{ $ml.get('provider_warn') }}</p>
                    </td>
                </tr>
                <tr>
                    <td width="30%">
                        <label class="label">{{ $ml.get('searchterms') }}</label>
                    </td>
                    <td>
                        <div class="control">
                        <input class="input" type="text" placeholder="" v-model="formdata.searchquery">
                        </div>
                        <p class="help is-danger is-hidden" ref="searchterms">{{ $ml.get('searchterms_warn') }}</p>
                    </td>
                </tr>

                <tr>
                    <td width="30%">
                        <label class="label">{{ $ml.get('requestreason') }}</label>
                    </td>
                    <td>
                        <textarea class="textarea" v-model="formdata.reason"></textarea>
                        <p class="help is-danger is-hidden" ref="reason">{{ $ml.get('requestreason_warn') }}</p>
                    </td>
                </tr>

                <tr>
                    <td width="30%">
                        <label class="label">{{ $ml.get('additionalinfo') }}</label>
                    </td>
                    <td>
                        <textarea class="textarea" v-model="formdata.info"></textarea>
                    </td>
                </tr>

                <tr>
                    <td width="30%">
                        <label class="label">{{ $ml.get('requestduration') }}</label>
                    </td>
                    <td>
                        <div class="control" style="flex-direction:inherit">
                            <span class="vertalign">{{ $ml.get('from') }} : </span><input type="date" class="input" style="width:170px" v-model="formdata.from" name="dp_fromdate" />
                            <span class="vertalign" style="margin-left:5px">{{ $ml.get('toandincluding') }} : </span><input type="date" class="input" style="width:170px" v-model="formdata.until" name="dp_untildate" />
                        </div>
                        <p class="help is-danger is-hidden" ref="duration">{{ $ml.get('requestduration_warn') }}</p>
                    </td>
                </tr>
            </table>
        </div>
        <div :class="step==1 ? '' : 'hidden'"> 
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
            <div v-html="$ml.get('requestanswerpos')"></div>
        </div>

        <div :class="step==3 ? '' : 'hidden'"> 
            <div v-html="$ml.get('requestanswerneg')">
                
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
                provider:"",
                title:"",
                searchquery:"",
                reason:"",
                info:"",
                from:"",
                until:""
            },
            providers:["Twitter","TikTok",""],
            step: 1
        }
    },
    computed: mapGetters(['getApiFormUrl','getUser','getUsername','getApiUrl']),
    created() {
        axios
            .get(this.getApiUrl+'/ddlists')
            .then(res => {
                this.providers = res.data.providers
            })
            .catch(error => {
                console.log(error)
            });
    },
    methods: {
        verzenden() {
            var error = 0;
            if (this.formdata.title.trim() == "") {
                this.$refs.title.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.title.classList.add('is-hidden')
            }
            if (this.formdata.provider.trim() == "") {
                this.$refs.provider.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.provider.classList.add('is-hidden')
            }
            if (this.formdata.searchquery.trim() == "") {
                this.$refs.searchterms.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.searchterms.classList.add('is-hidden')
            }
            if (this.formdata.reason.trim() == "") {
                this.$refs.reason.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.reason.classList.add('is-hidden')
            } 
            if (error > 0) return false;                      
            this.formdata.name = this.getUsername;
            this.formdata.email = this.getUser.email;
            var data = {"subject":"Aanvraag nieuwe dataset","formdata":this.formdata}
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
table td {
    border:0px
}

</style>