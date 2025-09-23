<template>
    <div>
        <label class="lowlabel" v-html="labelText"></label>
        <div class="scrollable" :style="'max-height:'+ this.height + 'px'">
            <div v-for="(v,k) in options" :key="k">
                <div v-if="v.id == undefined || v.id == ''">
                    {{ v.text }}
                </div>
                <div v-else>
                    <a @click="send(v.id)">{{ v.text }}</a>
                </div>
            </div>
        </div>
    </div>    
</template>
<script>
export default {
    props:{ label:{default:"",type:String},
            options:{type:Array},
            height:{default:320,type:Number}
            },
    data() {
        return {
        }
    },
    watch: {
    },
    computed:{
        labelText(){
            if (this.label == undefined || this.label == "") {
                return "&nbsp;"
            } else {
                return this.$ml.get(this.label)
            }
        }
    },
    methods: {
        send(id) {
            this.$emit("selectedId", id);
        }
    },
    created() {
    }
}
</script>
<style scoped>
.lowlabel {
    color:Black
}
.scrollable {
    overflow-y: auto; 
    padding:5px;
    color:black;
    border: 2px solid #757763;
    border-radius: 4px
}
.disabled {
    color:LightGray;
}
input {
    margin-right:4px
}
</style>