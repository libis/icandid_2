<template>
    <div>
        <h1 class="title">{{ $ml.get('admin')}} - {{ $ml.get('texts') }}</h1>
        <div v-if="mode=='list'">
            <table class="table is-hoverable is-fullwidth" v-if="content.length > 0">
                <tr>
                    <th>{{ $ml.get("title") }}</th>
                    <th></th>
                </tr>
                <tr v-for="(v,k) in content" :key="k">
                    <td>{{ title(v) }} </td>
                    <td><button class="button" @click="editEntry(k)">{{ $ml.get('edit')}}</button></td>
                </tr>
            </table>
        
        </div>
        <div v-if="mode=='editor'" class="box">
            <div class="tabs is-boxed">
            <ul>
                <li v-bind:class="{'is-active': (language=='nl')}" ><a @click.prevent.stop="language='nl'">{{ $ml.get('nl')}}</a></li>
                <li v-bind:class="{'is-active': (language=='en')}" ><a @click.prevent.stop="language='en'">{{ $ml.get('en')}}</a></li>
            </ul>
            </div>
            <div style="text-align: right;margin-top:-25px" v-if="!preview"><a  @click.prevent.stop="preview=true">{{ $ml.get('preview') }}</a></div>
            <div style="text-align: right;margin-top:-25px" v-if="preview"><a @click.prevent.stop="preview=false">{{ $ml.get('edit') }}</a></div>
            <div v-if="language=='nl'">
                <div v-if="!preview" class="field">
                    <div class="control">
                        <input  class="input" type="text" v-model="active.title_nl" style="width:1112px" maxlength="128">
                    </div>
                </div>
                <textarea v-if="!preview" class="markdownedit" id="editor_nl" v-model="active.content_nl" maxlength="65535"></textarea>
                <div v-if="preview">
                    <h3 class="title is-2" v-html="active.title_nl"></h3>
                    <MarkDown :markdown="active.content_nl"></MarkDown>
                </div>
            </div>
            <div v-if="language=='en'">
                <div v-if="!preview" class="field">
                    <div class="control">
                        <input  class="input" type="text" v-model="active.title_en" style="width:1112px" maxlength="128">
                    </div>
                </div>
                <textarea v-if="!preview" class="markdownedit" id="editor_en" v-model="active.content_en" maxlength="65535"></textarea>
                <div v-if="preview">
                    <h3 class="title is-2" v-html="active.title_en"></h3>
                    <MarkDown :markdown="active.content_en"></MarkDown>
                </div>
            </div>
            <br /><br />
            <div class="columns">
                <div class="column is-half">
                    
                </div>
                <div class="column is-one-quarter">
                    <a class="button is-primary" style="color:White" @click="saveEntry()">{{ $ml.get('save') }}</a>&nbsp;
                    <a class="button" @click="cancelEntry()">{{ $ml.get('cancel') }}</a>
                </div>
                <div class="column is-one-quarter">
                     
                </div>
            </div>            

        </div>
        <br /><br />
    </div>
</template>
<script>
import axios from "axios";
import { mapGetters } from "vuex";
axios.defaults.withCredentials = true;
import MarkDown from '../helpers/MarkDown.vue'
export default {
    components:{
        MarkDown
    },
    data() {
        return {
            active: {
                content_nl:"",
                content_en:"",
                code:"",
                id:0
            },
            content:[],
            mode:"list",
            preview:false,
            language:"nl"
        }
    },
    methods: {
        getContent() {
            axios
                .get(this.getApiAdminUrl + '/content')
                .then(res => {
                    this.content = res.data;
                })
                .catch(error => console.log(error));
        },
        editEntry(idx) {
            this.active.content_nl = this.content[idx].content_nl
            this.active.content_en = this.content[idx].content_en
            this.active.title_nl = this.content[idx].title_nl
            this.active.title_en = this.content[idx].title_en
            this.active.code = this.content[idx].code
            this.active.id = this.content[idx].id
            this.language='nl'
            this.mode='editor'
        },
        saveEntry() {
            axios
                .post(this.getApiAdminUrl + '/content', this.active)
                .then(res => {
                    this.content = res.data;
                })
                .catch(error => console.log(error));
            this.mode='list'
        },
        cancelEntry() {
            this.mode='list'
        },
        title(t) {
            return t['title_'+this.$ml.current]
        }
    },
    computed: mapGetters(['getApiAdminUrl']),
    created() {
        this.getContent();
    },
    watch: {
        mode: function() {
            if (this.mode == 'list') {
                this.preview = false
            }
        }
    }
}
</script>
<style>
.markdownedit {
    width:100%;
    height:500px;
    font-size:14px;
    padding:5px;
    color:black;
    border: 2px solid #757763;
    border-radius: 4px    ;
    margin-left:4px;
    
}
input[type="text"] {
    border-radius: 4px;
}
</style>