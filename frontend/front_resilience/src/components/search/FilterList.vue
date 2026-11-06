<template>
    <div v-if="bucket != undefined && bucket.length > 0" style="margin-bottom:18px">
        <div class="fl_head" @click="open = !open" :class="{ 'thickline' : open }">
          {{ $ml.get(type) }} ({{ bucket.length }})
          <img src="/img/chevron_dn.png" v-if="open">
          <img src="/img/chevron_up.png" v-if="!open">
        </div>
        <div class="fl_body" v-if="open">
            <a class="fl_selectall" @click="toggleall()">{{ (all?$ml.get('fl_deselectall'):$ml.get('fl_selectall')) }}</a>
            
            <ul class="content is-small" style="list-style-type: none; margin: 8px">
                <li v-for="(v,k) in bucket" :key="k">
                    <div class="field">
                    <input
                        type="checkbox"
                        class="is-checkradio is-small"
                        style="padding-bottom:30px"
                        ref="checkboxes"
                        :id="'f_'+type+'_'+v.key"
                        v-model=value
                        :value=v.key
                        :checked="value.includes(v.key)"
                        @change="updateSelection"
                    >                    
                    <label class="tablecell" :for="'f_'+type+'_'+v.key" v-html="makelabel(v,type)"></label>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>
<script>
export default {
    props: {
      value: {
        type: Array,
        default: () => []
      },
      type:{
        type:String
      },
      bucket:{
        type:Array
      }
    },
    data() {
      return {
        open:false,
        all:false
      }
    },
    methods:{
      makelabel(v,type) {
        if (type == "type") {
          return this.splitWritten(v.key)
        } else {
          return v.key
        }
      },
      splitWritten(t){
        var arr = t.split(/(?=[A-Z])/)
        for (var i=1;i<arr.length;i++) arr[i] = arr[i].toLowerCase()
        return arr.join(" ")
      },
      toggleall() {
        this.all = !this.all
        this.$refs.checkboxes.forEach( (cb) => {cb.checked = this.all})

        const selected = []
        if (this.all) {
            this.$refs.checkboxes.forEach( (cb) => { selected.push(cb.value); })
        }
        this.$emit('input', selected);
        this.$emit('change', selected);
       
      },

      updateSelection(event) {
        const selected = [...this.value];
        const item = event.target.value;
        if (event.target.checked) {
          if (!selected.includes(item)) {
            selected.push(item);
          }
        } else {
          const index = selected.indexOf(item);
          if (index !== -1) {
            selected.splice(index, 1);
          }
        }
        this.$emit('input', selected);
        this.$emit('change', selected);
      }
    },
    mounted () {
      
    }
}
</script>
<style>
.fl_head {
    display: flex;
    align-items: center;
    cursor:pointer;
    font-size:15px;
    border-bottom:1px solid #0625AC
}

.thickline {
  border-bottom:2px solid #0625AC
}
.fl_head img {
    margin-left: auto;
    width:12px
}

.fl_selectall {
  color: #0625AC;
  font-size:12px;
  padding-left:10px
}

</style>