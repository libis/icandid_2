<template>
    <div class="box">
        <h1 class="title">{{ $ml.get('requestAccessTitle') }}</h1>
        <p :class="step==1 ? '' : 'hidden'" v-html="$ml.get('requestAccessInfo')"></p>
        <br/>
        <div :class="step==1 ? '' : 'hidden'"> 
            <table border="0" cellspacing="3" cellpadding="0">
                <tr>
                    <td><label class="label">{{ $ml.get('name') }}</label></td>
                    <td>
                        <div class="control">
                        <input class="input" type="text" placeholder="" v-model="formdata.firstname">
                        </div>
                    </td>
                    <td>
                        <p class="help is-danger is-hidden" ref="firstname">{{ $ml.get('name_warn') }}</p>
                    </td>
                </tr>
                <tr>
                    <td><label class="label">{{ $ml.get('lastname') }}</label></td>
                    <td>
                        <div class="control">
                            <input class="input" type="text" placeholder="" v-model="formdata.lastname">
                        </div>
                    </td>
                    <td>
                        <p class="help is-danger is-hidden" ref="lastname">{{ $ml.get('lastname_warn') }}</p>
                    </td>
                </tr>
                <tr>
                    <td><label class="label">{{ $ml.get('email') }}</label></td>
                    <td>
                        <div class="control">
                            <input class="input" type="email" placeholder="" v-model="formdata.email">
                        </div>                    
                    </td>
                    <td><p class="help is-danger is-hidden" ref="email">{{ $ml.get('email_warn') }}</p></td>
                </tr>
                <tr>
                    <td><label class="label">{{ $ml.get('institution') }}</label></td>
                    <td>
                        <div class="control">
                            <select v-model="formdata.institution_idx" style="border-radius:4px">
                                <option></option>
                                <option v-for="(v,k) in orgs" v-bind:key="k" :value="k">{{ v.name }}</option>
                            </select>
                            
                        </div>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td><label class="label">{{ $ml.get('faculty') }}</label></td>
                    <td>
                        <div class="control">
                            <select v-model="formdata.faculty_id" v-if="(orgs[formdata.institution_idx].groups.length > 0)" style="border-radius:4px">
                                <option></option>
                                <optgroup v-for="(v,k) in orgs[formdata.institution_idx].groups" v-bind:key="k" :label="v.name">
                                    <option v-for="(vv,kk) in orgs[formdata.institution_idx].groups[k].faculty" v-bind:key="kk" :value="vv.id">{{ vv.name }}</option>
                                </optgroup>
                            </select>
                            <input class="input" type="text" placeholder="" v-model="formdata.faculty"  v-if="(orgs[formdata.institution_idx].groups.length == 0)">
                        </div>
                    </td>
                    <td><p class="help is-danger is-hidden" ref="faculty">{{ $ml.get('faculty_warn') }}</p></td>
                </tr>
                <tr>
                    <td><label class="label">{{ $ml.get('functiontitle') }}</label></td>
                    <td>
                        <label class="radio">
                            <input type="radio" name="function" value="Onderzoeker" v-model="formdata.function">
                            {{ $ml.get('researcher') }}
                        </label>
                        <label class="radio">
                            <input type="radio" name="function" value="Student" v-model="formdata.function">
                            {{ $ml.get('student') }}
                        </label>
                    </td>
                    <td><p class="help is-danger is-hidden" ref="function">{{ $ml.get('functiontitle_warn') }}</p></td>
                </tr>
                <tr>
                    <td><label class="label">{{ $ml.get('researchgroup') }}</label></td>
                    <td>
                        <div class="control">
                            <input class="input" type="text" placeholder="" v-model="formdata.researchgroup">
                        </div>
                    </td>
                    <td><p class="help is-danger is-hidden" ref="researchgroup">{{ $ml.get('researchgroup_warn') }}</p></td>
                </tr>
                <tr>
                    <td><label class="label">{{ $ml.get('promotor') }}</label></td>
                    <td><input class="input" type="text" placeholder="" v-model="formdata.promotor"></td>
                    <td><p class="help is-danger is-hidden" ref="promotor">{{ $ml.get('promotor_warn') }}</p></td>
                </tr>
                <tr>
                    <td><label class="label" style="text-wrap:none">{{ $ml.get('requestreason') }}</label></td>
                    <td><textarea class="textarea" style="width:300px" v-model="formdata.reason"></textarea></td>
                    <td><p class="help is-danger is-hidden" ref="reason">{{ $ml.get('requestreason_warn') }}</p></td>
                </tr>
            </table>

            <div class="field">
                <input type="checkbox" id="tos" v-model="formdata.termsofuse"><label for="tos" v-html="' ' + $ml.get('termsofuse')"></label>
            </div>
            <div class="field">
                <input type="checkbox" id="news" v-model="formdata.newsletter"><label for="news"> {{ $ml.get('requestnewsletter') }}</label>
            </div>
            <div class="columns">
                <div class="column is-half"></div>
                <div class="column is-half">
                    <a :disabled="!this.formdata.termsofuse" class="button is-primary" @click="verzenden()">
                    {{ $ml.get('sendrequest') }}
                    </a>
                </div>
            </div>
        </div>
        <div :class="step==4 ? '' : 'hidden'"> 
            <div v-html="$ml.get('requestanswerpos')"></div>
        </div>

        <div :class="step==5 ? '' : 'hidden'"> 
            <div v-html="$ml.get('requestanswerneg')">
                
            </div>
        </div>


    </div>
    
</template>
<script>
import axios from "../../../node_modules/axios";
import { mapGetters } from "../../../node_modules/vuex/dist/vuex.mjs";
axios.defaults.withCredentials = true;
export default {
    data() {
        return {
            formdata:{
                firstname:"",
                lastname:"",
                email:"",
                institution:"",
                institution_idx:0,
                function:"",
                promotor:"",
                faculty:"",
                faculty_id:0,
                researchgroup:"",
                loginname:"",
                reason:"",
                termsofuse:false,
                newsletter:true,
                language:this.$ml.current            },
            orgs:[],
            OrganizationDisplayName:"",
            step:1,
/*            dataset_query:{
                    "size": 0,
                    "query": {
                        "bool": {
                            "should": [
                                {"term": {"isBasedOn.provider.name.keyword": "Twitter"} }
                            ]
                        }
                    },
                    "aggs": {
                        "provider": {
                        "terms": {
                            "field": "isBasedOn.provider.name.keyword",
                            "size": 200,
                            "order": {
                            "_key": "asc"
                            }
                        },
                        "aggs": {
                            "dataset": {
                            "terms": {
                                "field": "isBasedOn.isPartOf.name.keyword",
                                "size": 200,
                                "order": {
                                "_key": "asc"
                                }
                            }
                            }
                        }
                        }
                    }
                },
            datasets:[]*/
        }
    },
    methods:{
        terug() {
            if (this.step > 1) this.step--;
        },
        validate() {
            var error = 0
            if (this.formdata.firstname.trim() == "") {
                this.$refs.firstname.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.firstname.classList.add('is-hidden')
            }
            if (this.formdata.lastname.trim() == "") {
                this.$refs.lastname.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.lastname.classList.add('is-hidden')
            }                
            var mail_format = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            if (this.formdata.email.match(mail_format)) {
                this.$refs.email.classList.add('is-hidden')
                
            } else {
                this.$refs.email.classList.remove('is-hidden')
                error++
            }
            if(this.formdata.faculty_id == 0 && this.formdata.faculty.trim() == "") {
                this.$refs.faculty.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.faculty.classList.add('is-hidden')
            }
            if (this.formdata.function.trim() == "" ) {
                this.$refs.function.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.function.classList.add('is-hidden')
            }
/*            if (this.formdata.promotor.trim() == "") {
                this.$refs.promotor.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.promotor.classList.add('is-hidden')
            } */
            if (this.formdata.researchgroup.trim() == "") {
                this.$refs.researchgroup.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.researchgroup.classList.add('is-hidden')
            }
            if (this.formdata.reason.trim() == "") {
                this.$refs.reason.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.reason.classList.add('is-hidden')
            }
            console.log(error)
            return (error == 0) 

        },
        verzenden() {
            if (!this.validate()) return true
            if (!this.formdata.termsofuse) return true
            this.formdata.institution = this.orgs[this.formdata.institution_idx].name

            this.orgs[this.formdata.institution_idx].groups.forEach(g => {
                g.faculty.forEach(f => {
                    if (f.id == this.formdata.faculty_id) {
                        this.formdata.faculty = f.name
                    }
                }) ;               
            });


            var formdata = JSON.parse(JSON.stringify(this.formdata))

            delete formdata.faculty_id
            delete formdata.institution_idx

            var data = {"subject":"Aanvraag toegang tot iCandid","formdata":formdata}
            axios
                .post(this.getApiFormUrl, data)
                .then(
                    this.step = 4
                )
                .catch(error => {
                    console.log(error)
                    this.step = 5
                });
        }

    },
    created() {

        axios
            .get(this.getApiUrl+'/jwt/decode')
            .then(res => {
                this.formdata.firstname = res.data.payload.given_name;
                this.formdata.lastname = res.data.payload.family_name;
                this.formdata.email = res.data.payload.email;
                this.formdata.loginname = res.data.payload.preferred_user;
                this.OrganizationDisplayName = res.data.payload.OrganizationDisplayName;

                axios
                    .get(this.getApiUrl+'/orgs')
                    .then(res => {
                        this.orgs = res.data
                        if (this.OrganizationDisplayName != "" && this.OrganizationDisplayName != undefined) {
                            var score = 0;
                            for (var i=0; i<this.orgs.length; i++) {
                                var s = this.$func.similarity(this.orgs[i].name, res.data.payload.OrganizationDisplayName)
                                if (s > score) {
                                    this.formdata.institution_idx = i;
                                    score = s;
                                } 
                            }
                        }
                    })
                    .catch(error => {
                        console.log(error)
                    });
            })
            .catch(error => {
                this.$cookies.set('wantsaccess',true,600);
                window.location.href='/login';
                console.log(error)
            });

/*
        axios
            .post(this.getApiQueryUrl, this.dataset_query)
            .then(res => {
                this.datasets = res.data.aggregations;
            })
            .catch(error => console.log(error));
*/
    },
    //computed: mapGetters(['getApiQueryUrl','getApiFormUrl','getApiUrl']),
    computed: mapGetters(['getApiFormUrl','getApiUrl']),
}
</script>
<style scoped>
input[type=text] {
    width:400px
}
input[type=email] {
    width:400px;
    border-radius:4px
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
.textarea {
    min-width: 80%;
    max-width:80%
}
.label {
    padding-right:5px;
    margin-top:5px
}
.radio {
    margin-top:8px
}

table td {
    border:0px
}
</style>