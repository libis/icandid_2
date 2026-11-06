<template>
    <div class="shelf">
        <div class="columns">
            <div class="column is-1 " style="display: flex; align-items: center; justify-content: center;">
                <div style="width:14px;cursor:pointer" @click="open =!open">
                    <Icon :icon="(open?'arrow-up':'arrow-down')" width="12" color="#202F3E"></Icon>
                </div>
                
            </div>
            <div class="column is-10 name"  style="cursor:pointer">
                <input class="name-edit" v-if="(edit || data.id == 0)" type="text" v-model="name" ref="namefield">
                <span v-else @click="open =!open" style="cursor:pointer">{{ data.name }}</span>

            </div>
            <div class="column is-1">
                <div v-if="(edit || data.id == 0)">
                    <div @click="done()" class="done-button">
                        <Icon icon="check" width="17" color="#202F3E"></Icon>
                        <span>{{ $ml.get('done') }}</span>
                    </div>
                </div>
                <div style="display: flex;gap:15px" v-else>
                    <span @click="letsedit()" style="cursor:pointer"><Icon icon="pen" width="17" color="#55718C" ></Icon></span>
                    <span @click="removeShelf()" style="cursor:pointer"><Icon icon="trash" width="17" color="#A3A3A3"></Icon></span>
                </div>
            </div>
        </div>
        <div class="shelflist" v-if="open">
            <table class="table fullwidth" width="100%" style="border-bottom:1px solid #dbdbdb">
                <tbody>
                    <tr width="100%" v-for="(rec, idx) in data.items" :key="'item_'+idx" >

                        <td style="padding:5px">
                            <div v-if="rec.value._source.associatedMedia == undefined || rec.value._source.associatedMedia.thumbnailUrl == undefined" class="iconcontainer" :style="'background:'+iconBgColors[rec.value._source['@type'].toLowerCase()]">
                            <Icon :color="iconColors[rec.value._source['@type'].toLowerCase()]" :icon="rec.value._source['@type']" width="23"></Icon> 
                            </div>
                            <div v-else class="iconcontainer" :style="'background:'+iconBgColors[rec.value._source['@type'].toLowerCase()]">
                            <img :src="rec.value._source.associatedMedia.thumbnailUrl" width="48">
                            </div>

                        </td> 
                        <td style="padding-left:10px;padding-top:0px;padding-bottom:5px;padding-right:8px">
                            <span class="rec_type" v-html="rec.value._source['@type']"></span> 
                            <p @click="showDetail(idx)" class="rec_name" v-if="rec.value._source.name !== undefined" v-html="parse(rec.value._source.name)"></p>
                            <p class="rec_author" v-if="rec.value._source.author !== undefined" v-html="parse(rec.value._source.author)"></p>
                        </td>
                        <td style="vertical-align:middle;padding-right:8px">
                            <p class="rec_publisher" v-if="rec.value._source.publisher !== undefined" v-html="parse(rec.value._source.publisher)"></p>
                        </td>
                        <td style="vertical-align:middle;padding-right:8px">
                            <p class="rec_dataset" v-if="rec.value._source.isBasedOn.isPartOf !== undefined" v-html="parse(rec.value._source.datePublished)"></p> 
                        </td>
                        <td style="vertical-align:middle;padding-right:8px">
                            <p class="rec_dataset" v-if="rec.value._source.isBasedOn.isPartOf !== undefined" v-html="parse(rec.value._source.isBasedOn.isPartOf.name)"></p> 
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <Dialog ref="dialog" @answer="onAnswer"></Dialog>
    </div>
</template>
<script>
import axios from 'axios';
import Icon from './Icon.vue'
import Dialog from './Dialog.vue'
import { mapGetters } from "vuex";
import { iconBgColors, iconColors } from '../../../constants.js';

export default {
    data() {
        return {
            open:false,
            edit:false,
            name:"",
            iconColors:{},
            iconBgColors:{},
        }
    },
    components:{
        Icon,
        Dialog
    },
    props: ['data'],
    methods: {
        parse(v) {
        if (Array.isArray(v)) {
            var l = Array()
            for (var i in v) {
            l.push(this.stripHTML(this.parse(v[i])))
            }
            return l.join('<br>')
        } else {
            if ((typeof v === 'string' || v instanceof String)) {
            return this.stripHTML(v)
            } else {
            if (v instanceof Object) {
                if (v.name != undefined) {
                if (v.name instanceof Object) {
                    if (v.name["@value"] != undefined) {
                    return this.stripHTML(v.name["@value"])
                    } else {
                    if (typeof v.name == 'string') {
                        return this.stripHTML(v.name)
                    }                  
                    }
                } else {
                    if (typeof v.name == 'string') {
                    return this.stripHTML(v.name)
                    }
                }
                }
                if (v["@value"] != undefined) {
                return this.stripHTML(v["@value"])
                }
            }
            }
        }
        return "not defined"
        },
        stripHTML(v) {
            if (typeof v != 'string') return v
            return v.replace(/(<([^>]+)>)/gi, "");
        },
        letsedit() {
            this.name = this.data.name
            this.edit=true
            this.$nextTick(() => {
                this.$refs.namefield.focus()
            })
        },
        done() {
            axios.post(this.getApiShelfUrl, {id:this.data.id, name:this.name}, {headers: {'Content-Type': 'application/ld+json'}})
            .then(res => {
                this.edit=false
                this.data.name = this.name
                this.$emit("shelfsaved", res.data)
            })
        },

        removeShelf() {
            this.$refs.dialog.confirm(this.$ml.get('confirm'),this.$ml.get('suredeleteset') + "'" + this.data.name + "'")
        },
        onAnswer(e) {
            if (e) {
            axios
                .delete(this.getApiShelfUrl + "/" + this.data.id)
                .then(
                this.$emit('deleted', this.data.id)
                )
                
                
                /*.catch(error => {
                console.log(error);
                })*/;
            }
        },
        showDetail(idx){
            console.log(idx)
            console.log(this.data.items[idx].value._source["@id"])
            this.$router.push('/record/' + this.data.items[idx].value._source["@id"])
        }

    },
    computed: {
    ...mapGetters(['getApiShelfUrl']), 
    },
    created() {
        if (this.data.id == 0)  { this.letsedit() }
    },
    mounted() {
        this.iconColors = iconColors
        this.iconBgColors = iconBgColors
    }
    
}
</script>
<style scoped>
.shelf {
    border-top:1px solid #D5DDE5;
    border-bottom:1px solid #D5DDE5;
    padding:10px;
}
.rec_type { 
    padding:2px; margin:0px ;
    border-radius: 4px;
    text-transform: capitalize;
    font-size:9px;
    font-weight:Normal;
    border:1px solid #ADBDCC;
    color:#455B71
}
.rec_name { 
    padding:0px; margin:0px ;
    font-weight:Bold;
    list-style-type:none;
    font-size: 16px;
}
p:hover.rec_name { 
  text-decoration: underline;
}

.iconcontainer {
  width:48px;height:64px; display: flex;  justify-content: center; align-items: center;
}
.rec_author { 
    padding:0px; margin:0px ;list-style-type:none;list-style-type:none;font-size:12px
}
.rec_publisher { 
    padding:0px; margin:0px ;
    font-style:Italic;
    list-style-type:none;;font-size:13px
}
.rec_dataset { 
    padding:0px; margin:0px ;list-style-type:none;list-style-type:none;font-size:11px
}
.name {
    font-weight: 700;
    font-size: 16px;
    line-height: 22px;
    letter-spacing: 0px;
    padding-top:15px

}
.done-button {
    color: #202F3E;
    background-color: #EBEFF3;
    cursor:pointer;
    display: flex;
    align-items: center;
    gap: 4px;
    height: 40px;
    padding: 0 12px 0 8px;
    border-radius: 6px;
    white-space: nowrap;
    width: fit-content;
    font-weight: 600;
    font-size: 13px;
    line-height: 18px;
    letter-spacing: 0%;
    text-align: center;
}
.name-edit {
    margin-top:13px;
    border:none;
    border-radius:0px;
    font-size:18px;
    width:75%
}
.name-edit:focus {
    border:none;
    outline:none;
}

</style>