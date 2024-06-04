<template>
    <div>
        <h1 class="title">{{ $ml.get('admin')}} - {{ $ml.get('groups') }}</h1>
        <div v-if="activeidx<0">
            <table class="table is-hoverable is-fullwidth" v-if="roleslist.length > 0">
                <tr>
                    <th>{{ $ml.get("fullname") }}</th>
                    <th></th>
                </tr>
                <tr v-for="(v,k) in roleslist" :key="k">
                    <td>{{ v.name }} </td>
                    <td><button class="button" @click="edit(k)">{{ $ml.get('edit')}}</button></td>
                </tr>
            </table>
            <button class="button" style="float:right" @click="edit(roleslist.length)">{{ $ml.get('newgroup') }}</button>
            <br/><br/>
        </div>
        <div class="box" v-if="activeidx >= 0">
            <div class="field">
                <label class="label">{{ $ml.get("fullname") }}</label>
                <div class="control">
                    <input class="input" type="text" v-model="activerole.name" maxlength="45">
                </div>
                <p class="help is-danger is-hidden" ref="namewarn">{{ $ml.get('fieldrequired') }}</p>
            </div>
            <label class="label">{{ $ml.get("accessrights") }}</label>
            <div class="columns">
                <div class="column is-half">
                    <CheckboxList label="collections_" :options="options.datasets" :selected="activerole.datasets" @select="getDatasets"></CheckboxList>
                </div>
                <div class="column is-half">
                    <CheckboxList label="functions" :options="options.resources" :selected="activerole.resources" @select="getResources"></CheckboxList>
                    <br/>
                    <div class="field">
                        <label class="label">{{ $ml.get("description") }}</label>
                        <div class="control">
                            <textarea class="textarea" v-model="activerole.description" maxlength="255"></textarea>
                        </div>
                    </div>

                </div>
            </div>
            <div class="columns">
                <div class="column is-half">
                    
                </div>
                <div class="column is-one-quarter">
                    <a class="button is-primary" style="color:White" @click="save()">{{ $ml.get('save') }}</a>&nbsp;
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
import CheckboxList from '../helpers/CheckboxList.vue'

export default {
    components:{
        CheckboxList
    },
    data() {
        return {
            roleslist:[],
            activeidx:-1,
            activerole:[],
            options:[],
            selectedidx:-1,
            selectedResources:[]
        }
    },
    methods: {
        update() {
            axios
                .get(this.getApiAdminUrl + '/groups')
                .then(res => {
                    this.roleslist = res.data;
                })
                .catch(error => console.log(error));
        },
        edit(idx) {
            this.activeidx = idx
            if(this.roleslist[this.activeidx] != null) {
                this.activerole = JSON.parse(JSON.stringify(this.roleslist[this.activeidx]));
                this.selectedDatasets = this.activerole.datasets.map(x => x.id)
                this.selectedResources = this.activerole.resources.map(x => x.id)
            } else {
                this.activerole = Object()
                this.activerole.id = 0;
                this.activerole.datasets = []
                this.activerole.resources = []
                this.activerole.name = ""
                this.selectedDatasets = []
                this.selectedResources = []
            }
            axios
                .get(this.getApiAdminUrl + '/options')
                .then(res => {
                    this.options = res.data;
                })
                .catch(error => console.log(error));            
        },
        save() {
            var error = 0;
            if (this.activerole.name == undefined || this.activerole.name.trim() == "") {
                this.$refs.namewarn.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.namewarn.classList.add('is-hidden')
            }    
            if (error > 0) return false
            axios
                .post(this.getApiAdminUrl + '/group', this.activerole)
                .then(res => {
                    this.activerole = res.data;
                    this.update();
                    this.edit(-1);
                    this.$root.$children[0].getUserInfo()
                })
                .catch(error => console.log(error));
        },
        getDatasets(value) {
            this.activerole.datasets = value
        },
        getResources(value) {
            this.activerole.resources = value
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
.field {
    margin-bottom:20px
}
textarea {
    width:540px;
}
.hidden {
    display:none;
}

</style>