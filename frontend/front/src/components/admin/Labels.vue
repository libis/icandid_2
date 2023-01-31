<template>
    <div>
        <h1 class="title">{{ $ml.get('admin')}} - {{ $ml.get('labels') }}</h1>
        <div v-if="activeidx<0">
            <table class="table is-hoverable is-fullwidth" v-if="labelslist.length > 0">
                <tr>
                    <th>{{ $ml.get("nl") }}</th>
                    <th>{{ $ml.get("en") }}</th>
                    <th></th>
                </tr>
                <tr v-for="(v,k) in labelslist" :key="k">
                    <td>{{ v.name_nl }} </td>
                    <td>{{ v.name_en }} </td>
                    <td><button class="button" @click="edit(k)">{{ $ml.get('edit')}}</button></td>
                </tr>
            </table>
            <button class="button" style="float:right" @click="edit(labelslist.length)">{{ $ml.get('newlabel') }}</button>
            <br/><br/>
        </div>
        <div class="box" v-if="activeidx >= 0">
            <div class="columns">
                <div class="column">
                    <div class="field">
                        <label class="label">{{ $ml.get("nl") }}</label>
                        <div class="control">
                            <input class="input" type="text" v-model="activelabel.name_nl" maxlength="45">
                        </div>
                        <p class="help is-danger is-hidden" ref="namenlwarn">{{ $ml.get('fieldrequired') }}</p>
                    </div>
                    <div class="field">
                        <label class="label">{{ $ml.get("en") }}</label>
                        <div class="control">
                            <input class="input" type="text" v-model="activelabel.name_en" maxlength="45">
                        </div>
                        <p class="help is-danger is-hidden" ref="nameenwarn">{{ $ml.get('fieldrequired') }}</p>
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="label">{{ $ml.get("description") }}</label>
                        <div class="control">
                            <textarea class="textarea" v-model="activelabel.description" maxlength="255"></textarea>
                        </div>
                    </div>

                </div>
            </div>

            <div class="columns">
                <div class="column is-half">
                    
                </div>
                <div class="column is-one-quarter">
                    <a class="button is-primary" @click="save()">{{ $ml.get('save') }}</a>&nbsp;
                    <a class="button" @click="edit(-1)">{{ $ml.get('cancel') }}</a>
                </div>
                <div class="column is-one-quarter">
                    
                </div>
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
            labelslist:[],
            activeidx:-1,
            activelabel:[],
        }
    },
    methods: {
        update() {
            axios
                .get(this.getApiAdminUrl + '/labels')
                .then(res => {
                    this.labelslist = res.data;
                })
                .catch(error => console.log(error));
        },
        edit(idx) {
            this.activeidx = idx
            if(this.labelslist[this.activeidx] != null) {
                this.activelabel = JSON.parse(JSON.stringify(this.labelslist[this.activeidx]));
            } else {
                this.activelabel = Object()
                this.activelabel.id = 0;
            }
        },
        save() {
            var error = 0;
            if (this.activelabel.name_nl == undefined || this.activelabel.name_nl.trim() == "") {
                this.$refs.namenlwarn.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.namenlwarn.classList.add('is-hidden')
            }
            if (this.activelabel.name_en == undefined || this.activelabel.name_en.trim() == "") {
                this.$refs.nameenwarn.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.nameenwarn.classList.add('is-hidden')
            }

            if (error > 0) return false; 
            axios
                .post(this.getApiAdminUrl + '/label', this.activelabel)
                .then(res => {
                    this.activelabel = res.data;
                    this.update();
                    this.edit(-1);
                    this.$root.$children[0].getUserInfo()
                })
                .catch(error => console.log(error));
        }
    },
    computed: mapGetters(['getApiAdminUrl']),
    created() {
        this.update();
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
textarea {
    width:400px
}
.field {
    margin-bottom:20px
}
.hidden {
    display:none;
}
</style>