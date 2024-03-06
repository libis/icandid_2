<template>
  <div>
    <div v-if="activeResult != undefined">
      <button class="button is-rounded" style="float: right;" :title="$ml.get('fullview')" v-if="$route.name != 'Record'" @click="fullview()"><i class="fa fa-file-o" style="margin-right:5px"></i>{{ $ml.get('fullview') }}</button>
      <div v-if="thumbnails().length>0 && $route.name == 'Record'" style="float: right;width:200px">
        <div v-for="(thumb,idx) in thumbnails()" :key="idx">
          <img :src="thumb"><br/>
        </div>
      </div>
      <div v-for="(field,idx) in fields" :key="idx">
          <div style="margin-bottom:.5em;line-height:1.2em;font-size:0.9em" v-if="show(idx) != ''" xv-if="activeResult._source[idx] != undefined">
            <div class="has-text-weight-semibold" >{{ field }} <button style="border:0px;background-color:transparent;font-size:16px" v-if="idx=='sameAs'" v-tooltip="info"><i class="fa fa-info"></i></button></div>
            <div v-if="idx != '@id' && idx != 'associatedMedia'" v-html="show(idx)" style="word-wrap:break-word" v-linkified></div>
            <div v-else v-html="show(idx)" style="word-wrap:break-word"></div>
          </div>
      </div>
      <div class="columns" v-if="$route.name != 'Record'">
        <div class="column"><button class="button is-rounded" :title="$ml.get('citation')" @click="citation()"><i class="fa fa-quote-left" style="margin-right:5px"></i>{{ $ml.get('citation') }}</button></div>
        <div class="column"><button class="button is-rounded" :title="$ml.get('email')" @click="email()"><i class="fa fa-envelope" style="margin-right:5px"></i>{{ $ml.get('email') }}</button></div>
        <div class="column"><button class="button is-rounded" :title="$ml.get('copylink')" @click="copylink()"><i class="fa fa-clipboard" style="margin-right:5px"></i>{{ $ml.get('copylink') }}</button></div>
      </div>
    </div>
    <Dialog ref="dialog"></Dialog>
    <EmailDialog ref="emaildialog"></EmailDialog>
    <CitationDialog ref="citationdialog"></CitationDialog>
  </div>
</template>
<script>

import { mapGetters } from "vuex";
import Dialog from '../helpers/Dialog.vue'
import EmailDialog from '../helpers/EmailDialog.vue'
import CitationDialog from '../helpers/CitationDialog.vue'

export default {
  data() {
    return {
      fields: {
        "@type": this.$ml.get('type_additionalType'),
        name: this.$ml.get('fullname'),
        honorificPrefix: this.$ml.get('honorificPrefix'),
        honorificSuffix: this.$ml.get('honorificSuffix'),
        familyName: this.$ml.get('familyName'),
        givenName: this.$ml.get('name'),
        additionalName: this.$ml.get('additionalName'),
        gender: this.$ml.get('gender'),
        nationality: this.$ml.get('nationality'),
        birthDate: this.$ml.get('birthDate'),
        birthPlace: this.$ml.get('birthPlace'),
        deathDate: this.$ml.get('deathDate'),
        deathPlace: this.$ml.get('deathPlace'),
        alumniOf: this.$ml.get('alumniOf'),
        associatedMedia:this.$ml.get('media'),
        description: this.$ml.get('description'),
        author: this.$ml.get('author'),
        creator: this.$ml.get('creator'),
        editor: this.$ml.get('editor'),
        contributor: this.$ml.get('contributor'),
        illustrator: this.$ml.get('illustrator'),
        translator: this.$ml.get('translator'),
        url: this.$ml.get('permalink'),
        sameAs: this.$ml.get('link'),
        license: this.$ml.get('type'),
        isBasedOn_license: this.$ml.get('license'),
        provider: this.$ml.get('provider'),
        isBasedOn_isPartOf: this.$ml.get('dataset'),
        isPartOf: this.$ml.get('isPartOf'),
        page: this.$ml.get('page'),
        numberOfPages: this.$ml.get('numberOfPages'),
        volume: this.$ml.get('volume'),
        issue: this.$ml.get('issue'),
        hasPart: this.$ml.get('hasPart'),
        dateCreated: this.$ml.get('dateCreated'),
        locationCreated: this.$ml.get('locationCreated'),
        datePublished:  this.$ml.get('publicationdate'),
        publisher: this.$ml.get('publication'),
        inLanguage:this.$ml.get('language'),
        keywords: this.$ml.get('keywords'),
        isbn: this.$ml.get('isbn'),
        issn: this.$ml.get('issn'),
        sdDatePublished: this.$ml.get('sdDatePublished'),
        sdPublisher: this.$ml.get('sdPublisher'),
        includedInDataCatalog: this.$ml.get('includedInDataCatalog'),
        startDate: this.$ml.get('startDate'),
        endDate: this.$ml.get('endDate'),
        about: this.$ml.get('about'),
        subjectOf: this.$ml.get('subjectOf'),  
        contentLocation:this.$ml.get('location'),
        spatialCoverage: this.$ml.get('spatialCoverage'),
        temporalCoverage: this.$ml.get('temporalCoverage'),
        mentions: this.$ml.get('mentions'),
        distribution: this.$ml.get('distribution'),
        copyright: this.$ml.get('copyright'),
        bookEdition: this.$ml.get('bookEdition'),
        material: this.$ml.get('material'),
        review: this.$ml.get('review'),
        itemReviewed: this.$ml.get('itemReviewed'),
        translationOfWork: this.$ml.get('translationOfWork'),
        workTranslation: this.$ml.get('workTranslation'),
        version: this.$ml.get('version'),
        "@id":  this.$ml.get('identifier')
//        legislationType: this.$ml.get('legislationType'),
//        publisher: this.$ml.get('publication'),
//        printEdition: this.$ml.get('edition'),
//        articleSection: this.$ml.get('articlesection'),
//        pagination: this.$ml.get('pagination'),
//        dateline: this.$ml.get('dateline'),
//        headline: this.$ml.get('title'),
//        sender:  this.$ml.get('sender'),
//        additionalType: this.$ml.get('additionalType'),
//        recipient:  this.$ml.get('recipient'),
//        legislationPassedBy: this.$ml.get('legislationPassedBy'),
//        legislationResponsible: this.$ml.get('legislationResponsible'),
//        articleBody:  this.$ml.get('text'),
//        text:  this.$ml.get('text'),
//        duration: this.$ml.get('duration'),
//        retweeted_tweet: this.$ml.get('retweeted_tweet'),
//        replied_to_tweet: this.$ml.get('replied_to_tweet'),
//        quoted_tweet: this.$ml.get('quoted_tweet'),
//        contentUrl: this.$ml.get('content'),
//        associatedMedia:this.$ml.get('media'),
//        updatetime:this.$ml.get('updatetime')
      },
      menuitems: [{ label: "Save item in set", name: "save" }],
      info: {
        content:this.$ml.get('dbinfotooltip'),
        html:true,
        trigger:'hover',
        autoHide:false,
      }
    };
  },
  props:['activeResult','currentLang','highlights'],
  components:{
    Dialog,
    EmailDialog,
    CitationDialog
  },
  methods: {
    nl2br(strvar) {
      return strvar.replace(/\n/g, "<br />");
    },
    format(str,idx) {
      if (typeof str == 'object') {
        if (str["@value"] != undefined) {
          str = str["@value"]
        }
      }

      if (idx === 'duration') {
        let duration = this.$moment.duration(str);
        let d = "";
        if (duration._data.hours > 0) {
          d = d + duration._data.hours.toString().padStart(2, "0") + ":";
        }
        d = d + duration._data.minutes.toString().padStart(2, "0") + ":" + duration._data.seconds.toString().padStart(2, "0");
        str = d;
      }

      if (this.getElasticQuery.highlight != undefined) {
        var search1 = this.getElasticQuery.highlight.pre_tags[0]
        var search2 = this.getElasticQuery.highlight.post_tags[0]
        var replace1 = search1.replace("<","[").replace(">","]")
        var replace2 = search2.replace("<","[").replace(">","]")

        str = str.replaceAll(search1,replace1)
        str = str.replaceAll(search2,replace2)
      }

      str = str.replaceAll("<","&lt;")
      str = str.replaceAll(">","&gt;")

      if (this.getElasticQuery.highlight != undefined) {
        str = str.replaceAll(replace1,search1)
        str = str.replaceAll(replace2,search2)
      }

      let out = "";

      const URLMatcher = /^(?:(?:https?):\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,}))\.?)(?::\d{2,5})?(?:[/?#]\S*)?$/igm
      if (idx == 'url') {
        out = str.replace(URLMatcher, match => `<a href="${match}">${match}</a>`);
      } else {
        out = str.replace(URLMatcher, match => `<a href="${match}" target="_blank">${match}</a>`);
      }
      return out;
    },
    show(idx) {
      var dat = "";
      var out = "";
      //console.log(idx)
      if (this.activeResult != undefined) {
        //console.log('a')
        if (this.activeResult._source[idx] != undefined) {
          //console.log('b')
          dat = this.activeResult._source[idx]
        } 
        //console.log(idx)
        if (idx.includes("_")) {
          //console.log('foobar')
          var idxs = idx.split("_")
          dat = this.activeResult._source[idxs[0]][idxs[1]]
          //console.log(dat)
        }

        if (typeof dat == 'string') {
          //console.log('f')
          out = this.format(dat, idx);
          
          if (idx == '@type') {
            //console.log('g')
            if (this.activeResult._source['additionalType'] != undefined && this.activeResult._source['additionalType'] != '') {
              //console.log('h')
              out += ", " + this.activeResult._source['additionalType'];
            }
          }
          if (idx == "sdDatePublished" || idx == "updatetime") {
            //console.log('i')
            var pubdat = new Date(dat)
            out = pubdat.toUTCString()
          }        
          return out
        }
        if (typeof dat == 'object') {
          //console.log('j')
          if (Array.isArray(dat)) {
            //console.log('k')
            for (var i in dat) {
              if (typeof dat[i] == 'string') {
                //console.log('l')
                out += "<div>" + this.format(dat[i],idx) + "</div>"
              } else {
                //console.log('m')
                var downloadFormat = "data:"+dat[i].encodingFormat + ";base64"


                if (dat[i]["@value"] != undefined) {
                  //console.log('n')
                  out += "<div>" + this.format(dat[i]["@value"],idx) + "</div>"
                } else {
                  //console.log('o')
                  if (dat[i].name != undefined) {
                    //console.log('p')
                    if ((idx == "legislationPassedBy" || idx == "legislationResponsible") && dat[i].memberOf != undefined && dat[i].memberOf.name != undefined) {
                      //console.log('q')
                      out += "<div>" + this.format(dat[i].name,idx) + " (" + dat[i].memberOf.name + ")</div>"
                    } else {
                      //console.log('r')
                      if (dat[i].contentUrl == undefined || dat[i].contentUrl.substring(0,downloadFormat.length) != downloadFormat) {
                        //console.log('s')
                        out += "<div>" + this.format(dat[i].name,idx) + "</div>"
                      }
                    }
                  }
                }
                if (dat[i]["@type"] == "MediaObject" || dat[i]["@type"] == "ImageObject") {
                  //console.log('t')
                  if (dat[i].contentUrl != undefined && dat[i].contentUrl.substring(0,downloadFormat.length) == downloadFormat) {
                    //console.log('u')
                    out += "<a download=\"" + dat[i].name + "\" href=\"" + dat[i].contentUrl+ "\">" + dat[i].name + "</a><br />"
                  } else {
/*                    if (dat[i].url != undefined) {
                      out += '<a target="_blank" href="' + dat[i].url + '">' + dat[i].url + '</a><br/>'
                    } */
                    //console.log('v')
                    if (dat[i].contentUrl != undefined) {
                      //console.log('w')
                      out += '<a target="_blank" href="' + dat[i].contentUrl + '">' + dat[i].contentUrl + '</a><br/>'
                    }
                    if (dat[i].thumbnailUrl != undefined) {
                      //console.log('x')
                      out += '<a target="_blank" href="' + dat[i].thumbnailUrl + '">' + dat[i].thumbnailUrl + '</a><br/>'
                    }
/*                    if (dat[i].width != undefined && dat[i].height != undefined) {
                      out += dat[i].width + 'x' + dat[i].height + '<br/>'
                    }
                    if (dat[i].encodingFormat != undefined) {
                      out += dat[i].encodingFormat + '<br/>'
                    } */
                  }
                }
              }
            }
          } else {
            //console.log('y')
            if (dat.name != undefined) {
              //console.log('z')
              if ((idx == "legislationPassedBy" || idx == "legislationResponsible") && dat.memberOf != undefined && dat.memberOf.name != undefined) {
                //console.log('A')
                out += this.format(dat.name,idx) + " (" + dat.memberOf.name + ")"
              } else {
                //console.log('B')
                out = this.format(dat.name, idx)
              }
            }
            if (dat["@value"] != undefined) {
              //console.log('C')
              out = this.format(dat["@value"], idx)
            }
          }

          if (idx == 'sender' || idx == 'recipient') {
            //console.log('D')
            out = out.replace("<div>","").replace("</div>","");
            if (this.activeResult != undefined) {
              //console.log('E')
              if (this.activeResult._source[idx] != undefined) {
                //console.log('F')
                var dat2 = this.activeResult._source[idx]
                if (dat2.alternateName != undefined) {
                  //console.log('G')
                  if (dat2.sameAs != undefined) {
                    //console.log('H')
                    out += "&nbsp;<a target=\"_blank\" href=\"" + dat2.sameAs + "\">@" + dat2.alternateName + "</a>"
                    
                  } else {
                    //console.log('I')
                    out += "&nbsp;@" + dat2.alternateName
                  }
                }
              }

            }
            out = "<div>" + out + "</div>"
          }

          if (idx == 'retweeted_tweet' || idx == 'replied_to_tweet' || idx == 'quoted_tweet') {
            //console.log('J')
            out = "<a href='" + dat["url"] + "' target='_blank'>" + dat["value"] + "</a>";
          }

          if (dat['@type'] == "Place" && dat["geo"] != undefined) {
            //console.log('K')
            for (const g of dat["geo"]) {
              if (g["@type"] == "GeoCoordinates") {
                //console.log('L')
                out += ' <a style="font-size:10px" target="_blank" href="https://geohack.toolforge.org/geohack.php?pagename='+encodeURI(out)+'&params=' + g.longitude + ';' + g.latitude + '">' + g.longitude.toFixed(2) + ',' + g.latitude.toFixed(2) + '</a>';
              }
            }
          }

          if (dat['@type'] == "MediaObject" || dat['@type'] == "ImageObject") {
/*            if (dat.url != undefined) {
              out += '<a target="_blank" href="' + dat.url + '">' + dat.url + '</a><br/>'
            } */
            //console.log('M')
            //out += '<br/>'
            if (dat.contentUrl != undefined) {
              //console.log('N')
              out += '<a target="_blank" href="' + dat.contentUrl + '">' + dat.contentUrl + '</a><br/>'
            }
            if (dat.thumbnailUrl != undefined) {
              //console.log('O')
              out += '<a target="_blank" href="' + dat.thumbnailUrl + '">' + dat.thumbnailUrl + '</a><br/>'
            }
/*            if (dat.width != undefined && dat.height != undefined) {
              out += dat.width + 'x' + dat.height + '<br/>'
            }
            if (dat.encodingFormat != undefined) {
              out += dat.encodingFormat + '<br/>'
            }*/
            
          }
          //console.log(out)
          return out
        } else {
          //console.log('P')
          return ""
        }
      }

      return idx + " : " + typeof dat;
    },
    copylink() {
      navigator.clipboard.writeText(this.activeResult._source.url)
        .then(() => { this.$refs.dialog.alert(this.$ml.get('info'),this.$ml.get('linkcopied')); })
        .catch((error) => { this.$refs.dialog.alert(this.$ml.get('info'),`${error}`) });
    },
    email(){
      this.$refs.emaildialog.open(this.activeResult);
    },
    citation() {
      this.$refs.citationdialog.open(this.activeResult);
    },
    fullview() {
      let route = this.$router.resolve({ path: "/record/" + this.activeResult._source['@id'] });
      window.open(route.href);
    },
    thumbnails() {
      var arr = []
      if (this.activeResult._source != undefined && this.activeResult._source.associatedMedia != undefined) {
        if (Array.isArray(this.activeResult._source.associatedMedia)) {
          for (var i in this.activeResult._source.associatedMedia) {
            arr.push(this.activeResult._source.associatedMedia[i].thumbnailUrl)
          }
        } else {
          if (this.activeResult._source.associatedMedia.thumbnailUrl != undefined) {
            arr.push(this.activeResult._source.associatedMedia.thumbnailUrl)
          }
        }
      }
      return arr
    },
    highlighter() {
      for (const i in this.activeResult.highlight) {
          for (const j in this.activeResult.highlight[i]) {
              this.changeByIndex(this.activeResult._source,i.split("."),this.activeResult.highlight[i][j])
          }
      }
    },
    isset(v) {
      return (v != undefined && v != null && v != "")
    },
    stripHTML(v) {
        if (typeof v != 'string') return v
        return v.replace(/(<([^>]+)>)/gi, "").replace(/[^0-9a-zA-Z]/gi, '');
    },
    changeByIndex(rec , idx, to) {
      if (this.isset(rec[idx[0]])){
        if (idx.length > 1) {
            if (typeof rec[idx[0]] === 'object') {
                if (rec[idx[0]] instanceof Array){
                    for (let i = 0; i < rec[idx[0]].length; i++) {
                        this.changeByIndex(rec[idx[0]][i],idx.slice(1),to)

                    }
                } else {
                    this.changeByIndex(rec[idx[0]],idx.slice(1),to)
                }
            } else {
                if (this.stripHTML(rec[idx[0]]) == this.stripHTML(to)) {
                    rec[idx[0]] = to
                }
            }
        } else {
            if (this.stripHTML(rec[idx[0]]) == this.stripHTML(to)) {
                rec[idx[0]] = to
            }
        }
      }
    }
  },
  watch : {
    activeResult : function() {
      if (this.activeResult != undefined) {
        if (this.highlights) {
          this.highlighter()
        }
        if (this.activeResult._source.name != undefined && this.activeResult._source.headline != undefined) {
          delete this.activeResult._source.headline
        }
      }
    }
  },
  computed: {
    ...mapGetters(["getElasticQuery"]),
  }
};
</script>
<style>
.highlighted {
/*  text-decoration:underline; */
  background-color: Yellow;
  padding-left:3px;
  padding-right:3px
}


</style>