<template>
    <div>
        <label class="lowlabel">{{ $ml.get(label) }}</label>
        <div style="float:right">
            <a v-if="selectedList.length == 0" @click.prevent.stop="selectAll()" style="font-size:12px">{{ $ml.get('selectevery')}}</a>
            <a v-if="selectedList.length != 0" @click.prevent.stop="deselectAll()" style="font-size:12px">{{ $ml.get('selectnone')}}</a>
        </div>
        <div class="scrollable">
            <div v-for="(v,k) in options" :key="k">
                <input v-if="v.via != undefined && v.via.length > 0" type="checkbox" class="checkbox" checked disabled style="" > 
                <input v-else type="checkbox" class="checkbox" v-model="selectedList" :value="v.id" :id="label+v.id"> 
                <label :for="label+v.id"> {{ format(v) }}</label>
            </div>
        </div>
    </div>    
</template>
<script>
export default {
    props:["label","options","selected"],
    data() {
        return {
            selectedList:[]
        }
    },
    watch: {
        selectedList() {
            this.$emit('select', this.options.filter(x => this.selectedList.includes(x.id) ))
        }
    },
    methods: {
        selectAll() {
            this.selectedList = this.options.map(x => x.id)                
        },
        deselectAll() {
            this.selectedList = []
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
    max-height:320px; 
    padding:5px;
    color:black;
    border: 2px solid #757763;
    border-radius: 4px
}
</style>