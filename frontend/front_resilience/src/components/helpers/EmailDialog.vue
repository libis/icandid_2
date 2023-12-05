<template>
<div class="modal" :class="(visible?'is-active':'')" >
  <div class="modal-background" @click="close()"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <div class="modal-card-title" v-html="$ml.get('email')"></div>
      <button class="delete" aria-label="close" @click="close()"></button>
    </header>
    <section class="modal-card-body spaced">
        <table cellpadding=20 cellspacing=2 width="100%">
            <tr>
              <td nowrap>Subject : </td><td width="100%">{{ this.mailsubject }}</td>
            </tr>
            <tr>
              <td>To : </td><td><input class="input" name="email" ref="email" style="width:80%" v-model="mailto"></td>
            </tr>
            <tr>
              <td nowrap>Message :&nbsp;</td><td><textarea class="input" rows=3 stylename="msg" ref="msg" style="width:80%;height:5.5em" v-model="mailmsg"></textarea></td>
            </tr>
        </table>

    </section>
    <footer class="modal-card-foot" style="justify-content: flex-end;">
      <button class="button is-success" @click="send()">{{ $ml.get('send') }}</button>
      <button class="button is-danger" @click="close()">{{ $ml.get('cancel') }}</button>
    </footer>
  </div>
  <Dialog ref="dialog" @answer="close()"></Dialog>
</div>    
</template>
<script>
import axios from "axios";
axios.defaults.withCredentials = true;
import { mapGetters } from "vuex";
import Dialog from '../helpers/Dialog.vue' 
export default {
    data() {
        return {
            visible:false,
            mailto:"",
            mailmsg:"",
            currentrecord:[]
        }
    },
    components: {
      Dialog
    },
    methods:{
        close() {
            this.visible = false;
        },
        open(current) {
            this.currentrecord = current
            this.visible = true;
        },
        send() {
          var data = Object();
          data["to"] = this.mailto;
          data["subject"] = this.mailsubject;
          data["body"] = '<a href="' + this.currentrecord._source.url + '">' + this.currentrecord._source.name["@value"] + '</a>\n\n'
          data["body"] += this.short(this.currentrecord._source) + "\n\n" + this.mailmsg;
          var url = this.getApiFormUrl+'/mail'
          axios.post(url, data)
            .then(() => {
              this.$refs.dialog.alert(this.$ml.get('info'),this.$ml.get('emailissent')); 
            })
            .catch(error => {
              this.$refs.dialog.alert(this.$ml.get('info'),`${error}`)
            }); 
        },
        short(cr) {
          var fields = {author:"Author(s)", contributor:"Contributor(s)", description:"Description", keywords:"Subject(s)"}
          var s = "<ul>"
          for (var k in fields){
            var v = fields[k]
            if (cr[k] != undefined) {
              s += "<li>" + v + "</li>"
              s += "<ul>"
              var flat = this.flatten(cr[k])
              for (var a in flat) {
                s+= "<li>" + flat[a] + "</li>"
              }
              s += "</ul>"
            }
          }
          s += "</ul>"
          return s
        },
        flatten(el) {
          var r = Array()
          if (Array.isArray(el) && typeof el != 'object') {
            for (var k in r) {
              r.push(this.flatten(r[k]))
            }
            return r
          } else {
            if (typeof el == 'string') {
              r.push(el) 
            } else {
              if (el.name != undefined) {
                if(typeof el.name == 'string') {
                  r.push(el.name)
                } else {
                  if (el.name["@value"] != undefined) {
                    if (typeof el.name["@value"] == 'string') {
                      r.push(el.name["@value"])
                    }
                  }
                }
              }
              if (el["@value"] != undefined) {
                if(typeof el["@value"] == 'string') {
                  r.push(el["@value"])
                }
              }
            }
          }
          return r
        }
    },
    computed: { ...mapGetters(['getApiFormUrl']),
        mailsubject: function() {
            if (this.currentrecord._source == undefined) {
              return ""
            }
            var maxlength = 80;
            var title = this.currentrecord._source.name['@value'];
            if (title.length > maxlength) {
                title = title.substr(0,maxlength)
            while (title.slice(-1) != " "){
                title = title.substr(0, title.length -1);
            } 
            title += '...';
            }
            return '[RESILIENCE] ' + title;
        }

    }
}
</script>
<style scoped>
.spaced {
    padding-top:40px;
    padding-bottom:50px
}
</style>