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
            <div v-if="language=='nl'">
                <div v-if="!preview" class="field">
                    <div class="control">
                        <input  class="input" type="text" v-model="active.title_nl" style="width:1092px" maxlength="128">
                    </div>
                </div>
                <vue-editor id="editor_nl" v-model="active.content_nl"></vue-editor>
            </div>
            <div v-if="language=='en'">
                <div v-if="!preview" class="field">
                    <div class="control">
                        <input  class="input" type="text" v-model="active.title_en" style="width:1092px" maxlength="128">
                    </div>
                </div>
                <vue-editor id="editor_en" v-model="active.content_en"></vue-editor>
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
import { VueEditor } from "vue2-editor";
import { marked } from 'marked'
const renderer = {
  heading(text, level) {
    return `
            <h${level} class="title is-${level}" style="margin-top:10px;">
              ${text}
            </h${level}>`;
  },
  link(href, title, text) {
    if (title == null) {
        return `<a href="${href}" target="_blank">${text}</a>`
    } else {
        return `<a href="${href}" title="${title}" target="_blank">${text}</a>`
    }
  },
  paragraph(text) {
    return `<p>${text}</p>`
  }
  
};

marked.use({ renderer });
export default {
    components:{
        VueEditor
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
            if (!this.isHTML(this.active.content_nl)) this.active.content_nl = this.myMarkDown(this.active.content_nl)
            if (!this.isHTML(this.active.content_en)) this.active.content_en = this.myMarkDown(this.active.content_en)
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
        },
        isHTML(str) {
            return (str.charAt(0) == "<")
        },
        myMarkDown(str) {
            if (str == null) {
                return ""
            }

            //var tmp = marked(this.markdown.replace(/\n/g, "<br />\n"))
            var tmp = marked(str)

            var re = /\{([a-z-]+)\}/ig
            var z = null
            var n = ""

            while(null != (z=re.exec(tmp))){
                n = "<i aria-hidden=\"true\" class=\"fa fa-" + z[1] + "\"></i>"
                tmp = tmp.replace(z[0],n)
            } 
            return tmp
        }
    },
    computed: mapGetters(['getApiAdminUrl']),
    created() {
        this.getContent();
    },
    watch: {
        
    }
}
</script>
<style>

input[type="text"] {
    border-radius: 4px;
}
#editor_nl,
#editor_en {
  height: 500px;
}
</style>