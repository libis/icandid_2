<template>
    <div>
        <label class="lowlabel" v-html="labelText"></label>
        <div style="float:right">
            <a v-if="selectEvery()" @click.prevent.stop="selectAll()" style="font-size:12px">{{ $ml.get('selectevery')}}</a>
            <a v-else @click.prevent.stop="deselectAll()" style="font-size:12px">{{ $ml.get('selectnone')}}</a>
        </div>
        <div class="scrollable" :style="'max-height:'+ this.height + 'px'">
            <div v-for="(v,k) in sortedOptions" :key="k">
                <input v-if="(v.via != undefined && v.via.length > 0)" type="checkbox" class="checkbox" checked disabled style="" > 
                <input v-else type="checkbox" class="checkbox" v-model="selectedList" :value="v.id" :id="label+v.id" :disabled="v.disabled" > 
                <label :class="{disabled: isDisabled || v.disabled}" :for="label+v.id"> {{ format(v) }}</label>
            </div>
        </div>
    </div>    
</template>
<script>
export default {
    props:{ label:{default:"",type:String},
            options:{type:Array},
            selected:{type:Array},
            height:{default:320,type:Number},
            isDisabled:{default:false, type:Boolean}
            },
    data() {
        return {
            selectedList:[]
        }
    },
    watch: {
        selectedList() {
            if (this.options != undefined) {
                this.$emit('select', this.options.filter(x => this.selectedList.includes(x.id) ))
            }
        }
    },
    computed:{
        sortedOptions() {
            if (this.options == undefined) return []
            var o = JSON.parse(JSON.stringify(this.options));
            var f = 'name'
            if (o[0] == undefined  || o[0].name == undefined) {
                f = 'name_'+this.$ml.current
            }
            return o.sort((a,b) => a[f].toUpperCase() >= b[f].toUpperCase())
        },
        labelText(){
            if (this.label == undefined || this.label == "") {
                return "&nbsp;"
            } else {
                return this.$ml.get(this.label)
            }
        }                

    },
    methods: {
        selectAll() {
            if (!this.isDisabled) { //this.selectedList = this.options.map(x => x.id)                
                this.options.forEach(el => {
                    if (!el.disabled) {
                        this.selectedList.push(el.id)
                    }
                    let s = new Set(this.selectedList)
                    this.selectedlist = [...s]
                });
            }
        },
        deselectAll() {
            if (!this.isDisabled) { //this.selectedList = []
                let l = this.selectedList.length -1;
                for(var i = l;i>=0;i--){
                    let obj = this.options.find(o => o.id == this.selectedList[i])
                    if (!obj.disabled){
                        this.selectedList.pop(i)
                    }
                }

            }
        },
        format(v) {
            var f = v.name 
            if (f == undefined) {
                f = v['name_'+this.$ml.current] 
            }
            if (v.via != undefined) { 
                if (v.via.length > 0) {
                    f += " (" + v.via.sort().join(', ') + ")"
                }
            }
            return f
        },
        selectEvery() {
            if (this.options == undefined) { return false }
            let c = 0
            this.options.forEach(el => {
                if (this.selectedList.includes(el.id) && !el.disabled) { c++ }
            })
            return c == 0
        }
    },
    created() {
        this.selectedList = this.selected.map(x => x.id)
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
    cursor: not-allowed;
}
input {
    margin-right:4px
}
</style>