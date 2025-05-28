<template>
    <div class="box">
        <h1 class="title">{{ $ml.get('requestAccessTitle') }}</h1>
        <p v-html="$ml.get('requestAccessInfo')"></p>
        <br/>
        <div :class="step==1 ? '' : 'hidden'"> 
            <div class="field">
            <label class="label">{{ $ml.get('name') }}</label>
            <div class="control">
                <input class="input" type="text" placeholder="" v-model="formdata.firstname">
            </div>
            <p class="help is-danger is-hidden" ref="firstname">{{ $ml.get('name_warn') }}</p>
            </div>            

            <div class="field">
            <label class="label">{{ $ml.get('lastname') }}</label>
            <div class="control">
                <input class="input" type="text" placeholder="" v-model="formdata.lastname">
            </div>
            <p class="help is-danger is-hidden" ref="lastname">{{ $ml.get('lastname_warn') }}</p>
            </div>            

            <div class="field">
            <label class="label">{{ $ml.get('email') }}</label>
            <div class="control">
                <input class="input" type="email" placeholder="" v-model="formdata.email">
            </div>
            <p class="help is-danger is-hidden" ref="email">{{ $ml.get('email_warn') }}</p>
            </div>            

            <div class="field">
            <label class="label">{{ $ml.get('institution') }}</label>                
            <label class="radio">
                <input type="radio" name="institution" value="KU Leuven" v-model="formdata.institution">
                {{ $ml.get('kuleuven')}}
            </label>
            <label class="radio">
                <input type="radio" name="institution" value="Andere" v-model="formdata.institution">
                {{ $ml.get('other')}}
            </label>
            </div>
        </div>

        <div :class="step==2 ? '' : 'hidden'"> 
            <div class="field" v-if="formdata.institution != 'KU Leuven'">
            <label class="label">{{ $ml.get('institutionname') }}</label>
            <div class="control">
                <input class="input" type="text" placeholder="" v-model="formdata.institutionname">
            </div>
            <p class="help is-danger is-hidden" ref="institutionname">{{ $ml.get('institutionname_warn') }}</p>
            </div>            

            <div class="field" v-if="formdata.institution == 'KU Leuven'">
            <label class="label">{{ $ml.get('functiontitle') }}</label>                
            <label class="radio">
                <input type="radio" name="function" value="Onderzoeker" v-model="formdata.function">
                {{ $ml.get('researcher') }}
            </label>
            <label class="radio">
                <input type="radio" name="function" value="Student" v-model="formdata.function">
                {{ $ml.get('student') }}
            </label>
            <p class="help is-danger is-hidden" ref="function">{{ $ml.get('functiontitle_warn') }}</p>
            </div>

            <div class="field" v-if="formdata.institution == 'KU Leuven' && formdata.function == 'Student'">
            <label class="label">{{ $ml.get('promotor') }}</label>
            <div class="control">
                <input class="input" type="text" placeholder="" v-model="formdata.promotor">
            </div>
            <p class="help is-danger is-hidden" ref="promotor">{{ $ml.get('promotor_warn') }}</p>
            </div>            

            <div class="field" v-if="formdata.institution == 'KU Leuven'">
            <label class="label">{{ $ml.get('faculty') }}</label>
            <div class="select">
            <select v-model="formdata.faculty">
                <optgroup label="Groep Humane Wetenschappen">
                    <option>Faculteit Theologie en Religiewetenschappen</option>
                    <option>Bijzondere Faculteit Kerkelijk Recht</option>
                    <option>Hoger Instituut voor Wijsbegeerte</option>
                    <option>Faculteit Rechtsgeleerdheid en Criminologische Wetenschappen</option>
                    <option>Faculteit Economie en Bedrijfswetenschappen</option>
                    <option>Faculteit Sociale Wetenschappen</option>
                    <option>Faculteit Letteren</option>
                    <option>Faculteit Psychologie en Pedagogische Wetenschappen</option>
                    <option>Doctoral School for the Humanities and Social Sciences</option>
                </optgroup>
                <optgroup label="Groep Wetenschap & Technologie">
                    <option>Faculteit Wetenschappen</option>
                    <option>Faculteit Ingenieurswetenschappen</option>
                    <option>Faculteit Bio-ingenieurswetenschappen</option>
                    <option>Faculteit Industriële Ingenieurswetenschappen</option>
                    <option>Faculteit Architectuur </option>
                    <option>Arenberg Doctoral School of Science, Engineering & Technology </option>
                </optgroup>
                <optgroup label="Groep Biomedische Wetenschappen">
                    <option>Faculteit Geneeskunde</option>
                    <option>Faculteit Farmaceutische Wetenschappen</option>
                    <option>Faculteit Bewegings- en Revalidatiewetenschappen</option>
                    <option>Doctoral School of Biomedical Sciences</option>
                </optgroup>
            </select>
            </div>
            <p class="help is-danger is-hidden" ref="faculty">{{ $ml.get('faculty_warn') }}</p>
            </div>

            <div class="field">
            <label class="label">{{ $ml.get('researchgroup') }}</label>
            <div class="control">
                <input class="input" type="text" placeholder="" v-model="formdata.researchgroup">
            </div>
            <p class="help is-danger is-hidden" ref="researchgroup">{{ $ml.get('researchgroup_warn') }}</p>
            </div>            

            <div class="field" v-if="formdata.institution == 'KU Leuven'">
            <label class="label">{{ $ml.get('personel_student_no') }}</label>
            <div class="control">
                <input class="input" type="text" placeholder="" v-model="formdata.loginname">
            </div>
            <p class="help is-danger is-hidden" ref="loginname">{{ $ml.get('personel_student_no_warn') }}</p>
            </div>            


            <div class="field">
            <label class="label" style="text-wrap:none">{{ $ml.get('requestreason') }}</label>
            <textarea class="textarea" style="width:300px" v-model="formdata.reason"></textarea>
            <p class="help is-danger is-hidden" ref="reason">{{ $ml.get('requestreason_warn') }}</p>
            </div>

            <div class="field" v-if="formdata.institution != 'KU Leuven'">
            <label class="label">{{ $ml.get('requestduration') }}</label>
            <div class="control" style="flex-direction:inherit">
                <span class="vertalign">{{ $ml.get('from') }} : </span><input type="date" class="input" style="width:170px" v-model="formdata.from" name="dp_fromdate" />
                <span class="vertalign" style="margin-left:5px">{{ $ml.get('toandincluding') }} : </span><input type="date" class="input" style="width:170px" v-model="formdata.until" name="dp_untildate" />
            </div>
            <p class="help is-danger is-hidden" ref="duration">{{ $ml.get('requestduration_warn') }}</p>
            </div>
            <div class="field">
            <label class="label">{{ $ml.get('requestfunctionality') }}</label>
            <div class="select">
            <select v-model="formdata.functionality">
                    <option>User Interface</option>
                    <option>User Interface + export</option>
                    <option>API</option>
                    <option>API + User Interface + export</option>
            </select>
            </div>
            <p class="help is-danger is-hidden" ref="functionality">{{ $ml.get('chooseone') }}</p>
            </div>
        </div>

        <div :class="step==3 ? '' : 'hidden'"> 
            <div class="field">
                <input type="checkbox" id="tos" v-model="formdata.termsofuse"><label for="tos" v-html="' ' + $ml.get('termsofuse')"></label>
            </div>
            <div class="field">
                <input type="checkbox" id="news" v-model="formdata.newsletter"><label for="news"> {{ $ml.get('requestnewsletter') }}</label>
            </div>
        </div>

        <div :class="step==4 ? '' : 'hidden'"> 
            <div v-html="$ml.get('requestanswerpos')"></div>
        </div>

        <div :class="step==5 ? '' : 'hidden'"> 
            <div v-html="$ml.get('requestanswerneg')">
                
            </div>
        </div>

        <div class="columns">
            <div class="column is-half">
                <a class="button" v-if="step!=1 && step < 4" @click="terug()">&lt;&lt; {{ $ml.get('back')}}</a>
            </div>
            <div class="column is-half">
                <a class="button" v-if="step < 3" @click="verder()">
                {{ $ml.get('next') }} &gt;&gt;
                </a>
                <a :disabled="!this.formdata.termsofuse" class="button is-primary" v-if="step==3" @click="verzenden()">
                {{ $ml.get('sendrequest') }}
                </a>
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
                institution:"KU Leuven",
                institutionname:"",
                function:"",
                promotor:"",
                faculty:"",
                researchgroup:"",
                loginname:"",
                reason:"",
                from:"",
                until:"",
                functionality:"",
                termsofuse:false,
                newsletter:true,
                language:this.$ml.current            },
            step:1,
            dataset_query:{
                    "size": 0,
                    "query": {
                        "bool": {
                            "should": [
                                {"term": {"isBasedOn.provider.name.keyword": "Twitter"} }
//                                ,{"term": {"isBasedOn.provider.name.keyword": "extra"} }    // lijntje als aanduiding hoe uit te breiden met extra providers
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
            datasets:[]
        }
    },
    methods:{
        terug() {
            if (this.step > 1) this.step--;
        },
        verder() {
            this.$cookies.remove('wantsaccess');
            var error = 0
            if (this.step == 1) {
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
            }
            if (this.step == 2) {
                if (this.formdata.institution != "KU Leuven") {
                    if (this.formdata.institutionname.trim() == "") {
                        this.$refs.institutionname.classList.remove('is-hidden')
                        error++
                    } else {
                        this.$refs.institutionname.classList.add('is-hidden')
                    }
                }

                if (this.formdata.institution == "KU Leuven") {
                    if (this.formdata.function.trim() == "" ) {
                        this.$refs.function.classList.remove('is-hidden')
                        error++
                    } else {
                        this.$refs.function.classList.add('is-hidden')
                    }
                }
                if (this.formdata.institution == 'KU Leuven' && this.formdata.function == 'Student') {
                    if (this.formdata.promotor.trim() == "") {
                        this.$refs.promotor.classList.remove('is-hidden')
                        error++
                    } else {
                        this.$refs.promotor.classList.add('is-hidden')
                    }
                }

                if (this.formdata.institution == "KU Leuven") {
                    if (this.formdata.faculty.trim() == "" ) {
                        this.$refs.faculty.classList.remove('is-hidden')
                        error++
                    } else {
                        this.$refs.faculty.classList.add('is-hidden')
                    }
                }

                if (this.formdata.researchgroup.trim() == "") {
                    this.$refs.researchgroup.classList.remove('is-hidden')
                    error++
                } else {
                    this.$refs.researchgroup.classList.add('is-hidden')
                }

                if (this.formdata.institution == "KU Leuven") {
                    if (this.formdata.loginname.trim() == "" ) {
                        this.$refs.loginname.classList.remove('is-hidden')
                        error++
                    } else {
                        this.$refs.loginname.classList.add('is-hidden')
                    }
                }

                if (this.formdata.reason.trim() == "") {
                    this.$refs.reason.classList.remove('is-hidden')
                    error++
                } else {
                    this.$refs.reason.classList.add('is-hidden')
                }

                if (this.formdata.institution != "KU Leuven") {
                    if (this.formdata.from.toString().trim() == "" || this.formdata.until.toString().trim() == "") {
                        this.$refs.duration.classList.remove('is-hidden')
                        error++
                    } else {
                        this.$refs.duration.classList.add('is-hidden')
                    }
                }

                if (this.formdata.functionality.trim() == "") {
                    this.$refs.functionality.classList.remove('is-hidden')
                    error++
                } else {
                    this.$refs.functionality.classList.add('is-hidden')
                }


            }

            if (this.step == 3) {
                if (this.formdata.collectionregister=='Ja') {
                    if (this.formdata.datasets.length == 0) {
                        this.$refs.datasets.classList.remove('is-hidden')
                        error++                        
                    } else {
                        this.$refs.datasets.classList.add('is-hidden')
                    }
                }

            }

            if (error > 0) return true
            if (this.step < 3) this.step++;
        },
        verzenden() {
            if (!this.formdata.termsofuse) return true
            if (this.formdata.institution == 'KU Leuven') {
                this.formdata.institutionname = "";
                this.formdata.from = "";
                this.formdata.until = "";
                //this.formdata.media2 = "";
            }
/*
            if (this.formdata.institution != 'KU Leuven') {
                this.formdata.faculty = "";
                this.formdata.loginname = "";
                this.formdata.media1 = "";
                this.formdata.function = "";
            }
*/
            if (this.formdata.function != 'Student') {
                this.formdata.promotor = "";
            }
            if (this.formdata.collectionregister == 'Nee') {
                this.formdata.datasets = []
            }

            var data = {"subject":"Aanvraag toegang tot iCandid","formdata":this.formdata}
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
            })
            .catch(error => {
                this.$cookies.set('wantsaccess',true,600);
                window.location.href='/login';
                console.log(error)
            });


        axios
            .post(this.getApiQueryUrl, this.dataset_query)
            .then(res => {
                this.datasets = res.data.aggregations;
            })
            .catch(error => console.log(error));
    },
    computed: mapGetters(['getApiQueryUrl','getApiFormUrl','getApiUrl']),
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
</style>