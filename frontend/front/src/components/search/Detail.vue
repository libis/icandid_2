<template>
  <div>
    <div v-if="activeResult != undefined">
      <div v-for="(field,idx) in fields" :key="idx">
          <div style="margin-bottom:.5em" v-if="activeResult._source[idx] != undefined">
            <div class="has-text-weight-semibold" >{{ field }} <button style="border:0px;background-color:transparent;font-size:16px" v-if="idx=='sameAs'" v-tooltip="info"><i class="fa fa-info"></i></button></div>
            <div v-if="idx != '@id' && idx != 'associatedMedia'" v-html="show(idx)" style="word-wrap:break-word" v-linkified></div>
            <div v-else v-html="show(idx)" style="word-wrap:break-word"></div>
          </div>
      </div>
    </div>
    
  </div>
</template>
<script>

import { mapGetters } from "vuex";

export default {
  data() {
    return {
      fields: {
        "@type": this.$ml.get('type'),
        "@id":  this.$ml.get('identifier'),
        legislationType: this.$ml.get('legislationType'),
        publisher: this.$ml.get('publication'),
        printEdition: this.$ml.get('edition'),
        articleSection: this.$ml.get('articlesection'),
        pagination: this.$ml.get('pagination'),
        datePublished:  this.$ml.get('publicationdate'),
        dateline: this.$ml.get('dateline'),
        headline: this.$ml.get('title'),
        creator: this.$ml.get('author'),
        sender:  this.$ml.get('sender'),
        recipient:  this.$ml.get('recipient'),
        legislationPassedBy: this.$ml.get('legislationPassedBy'),
        legislationResponsible: this.$ml.get('legislationResponsible'),
        description: this.$ml.get('description'),
        articleBody:  this.$ml.get('text'),
        text:  this.$ml.get('text'),
        keywords: this.$ml.get('keywords'),
        mentions: this.$ml.get('mentions'),
        duration: this.$ml.get('duration'),
//        retweeted_tweet: this.$ml.get('retweeted_tweet'),
//        replied_to_tweet: this.$ml.get('replied_to_tweet'),
//        quoted_tweet: this.$ml.get('quoted_tweet'),
        contentUrl: this.$ml.get('content'),
        about: this.$ml.get('about'),
        inLanguage:this.$ml.get('language'),
        contentLocation:this.$ml.get('location'),
        associatedMedia:this.$ml.get('media'),
        sameAs: this.$ml.get('link'),
        url: this.$ml.get('permalink'),
        provider: this.$ml.get('provider'),
        sdDatePublished: this.$ml.get('sdDatePublished'),
        updatetime:this.$ml.get('updatetime')
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
  methods: {
    nl2br(strvar) {
      return strvar.replace(/\n/g, "<br />");
    },
    format(str,idx) {
      
//      console.log(idx + ' ' + str)
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
//      console.log(idx)
      var dat = "";
      var out = "";
      if (this.activeResult != undefined) {
        if (this.activeResult._source[idx] != undefined) {
          dat = this.activeResult._source[idx]
        }

        if (this.highlights) {
          if (this.activeResult.highlight != undefined && this.activeResult.highlight[idx+'.@value'] != undefined) {
            dat = this.activeResult.highlight[idx+'.@value']
          } 

          if (this.activeResult.highlight != undefined && this.activeResult.highlight[idx+'.name'] != undefined) {
            dat = this.activeResult.highlight[idx+'.name']
          }

        }
        if (typeof dat == 'string') {
          out = this.format(dat, idx);
          if (idx == '@type') {
            if (this.activeResult._source['additionalType'] != undefined && this.activeResult._source['additionalType'] != '') {
              out += ", " + this.activeResult._source['additionalType'];
            }
          }
          if (idx == "sdDatePublished" || idx == "updatetime") {
            var pubdat = new Date(dat)
            out = pubdat.toUTCString()
          }        
          return out
        }
        if (typeof dat == 'object') {
          if (Array.isArray(dat)) {
            for (var i in dat) {
              if (typeof dat[i] == 'string') {
                out += "<div>" + this.format(dat[i],idx) + "</div>"
              } else {
                var downloadFormat = "data:"+dat[i].encodingFormat + ";base64"


                if (dat[i]["@value"] != undefined) {
                  out += "<div>" + this.format(dat[i]["@value"],idx) + "</div>"
                } else {
                  if (dat[i].name != undefined) {
                    if ((idx == "legislationPassedBy" || idx == "legislationResponsible") && dat[i].memberOf != undefined && dat[i].memberOf.name != undefined) {
                      out += "<div>" + this.format(dat[i].name,idx) + " (" + dat[i].memberOf.name + ")</div>"
                    } else {
                      if (dat[i].contentUrl == undefined || dat[i].contentUrl.substring(0,downloadFormat.length) != downloadFormat) {
                        out += "<div>" + this.format(dat[i].name,idx) + "</div>"
                      }
                    }
                  }
                }
                if (dat[i]["@type"] == "MediaObject" || dat[i]["@type"] == "ImageObject") {
                  if (dat[i].contentUrl.substring(0,downloadFormat.length) == downloadFormat) {
                    out += "<a download=\"" + dat[i].name + "\" href=\"" + dat[i].contentUrl+ "\">" + dat[i].name + "</a><br />"
                  } else {
                    console.log("hier mag ik niet zijn")
                    if (dat[i].url != undefined) {
                      out += '<a target="_blank" href="' + dat[i].url + '">' + dat[i].url + '</a><br/>'
                    }
                    if (dat[i].contentUrl != undefined) {
                      out += '<a target="_blank" href="' + dat[i].contentUrl + '">' + dat[i].contentUrl + '</a><br/>'
                    }
                    if (dat[i].thumbnailUrl != undefined) {
                      out += '<a target="_blank" href="' + dat[i].thumbnailUrl + '">' + dat[i].thumbnailUrl + '</a><br/>'
                    }
                    if (dat[i].width != undefined && dat[i].height != undefined) {
                      out += dat[i].width + 'x' + dat[i].height + '<br/>'
                    }
                    if (dat[i].encodingFormat != undefined) {
                      out += dat[i].encodingFormat + '<br/>'
                    }
                  }
                }
              }
            }
          } else {
            if (dat.name != undefined) {
              if ((idx == "legislationPassedBy" || idx == "legislationResponsible") && dat.memberOf != undefined && dat.memberOf.name != undefined) {
                out += this.format(dat.name,idx) + " (" + dat.memberOf.name + ")"
              } else {
                out = this.format(dat.name, idx)
              }
            }
            if (dat["@value"] != undefined) {
              out = this.format(dat["@value"], idx)
            }
          }

          if (idx == 'sender' || idx == 'recipient') {
            if (dat.alternateName != undefined) {
              if (dat.sameAs != undefined) {
                out += "&nbsp;<a target=\"_blank\" href=\"" + dat.sameAs + "\">@" + dat.alternateName + "</a>"
              } else {
                out += "&nbsp;@" + dat.alternateName
              }
            }

          }

          if (idx == 'retweeted_tweet' || idx == 'replied_to_tweet' || idx == 'quoted_tweet') {
            out = "<a href='" + dat["url"] + "' target='_blank'>" + dat["value"] + "</a>";
          }

          if (dat['@type'] == "Place" && dat["geo"] != undefined) {
            for (const g of dat["geo"]) {
              if (g["@type"] == "GeoCoordinates") {
                out += ' <a style="font-size:10px" target="_blank" href="https://geohack.toolforge.org/geohack.php?pagename='+encodeURI(out)+'&params=' + g.longitude + ';' + g.latitude + '">' + g.longitude.toFixed(2) + ',' + g.latitude.toFixed(2) + '</a>';
              }
            }
          }

          if (dat['@type'] == "MediaObject" || dat['@type'] == "ImageObject") {
            if (dat.url != undefined) {
              out += '<a target="_blank" href="' + dat.url + '">' + dat.url + '</a><br/>'
            }
            if (dat.contentUrl != undefined) {
              out += '<a target="_blank" href="' + dat.contentUrl + '">' + dat.contentUrl + '</a><br/>'
            }
            if (dat.thumbnailUrl != undefined) {
              out += '<a target="_blank" href="' + dat.thumbnailUrl + '">' + dat.thumbnailUrl + '</a><br/>'
            }
            if (dat.width != undefined && dat.height != undefined) {
              out += dat.width + 'x' + dat.height + '<br/>'
            }
            if (dat.encodingFormat != undefined) {
              out += dat.encodingFormat + '<br/>'
            }
            
          }

          return out
        } else {
          return ""
        }
      }

      return idx + " : " + typeof dat;
    },
    setExtraTweets() {
      if (this.activeResult != undefined) {
        if (this.activeResult._source["identifier"] != undefined) {
          for(var elidx in this.activeResult._source["identifier"]) {
            var element = this.activeResult._source["identifier"][elidx];
            if (element["name"] == "retweeted_tweet_id") {
              this.activeResult._source["retweeted_tweet"] = element;
            }
            if (element["name"] == "quoted_tweet_id") {
              this.activeResult._source["quoted_tweet"] = element;
            }
            if (element["name"] == "replied_to_tweet_id") {
              this.activeResult._source["replied_to_tweet"] = element;
            }
          }
        }
      }
    }
  },
  watch : {
    activeResult : function() {
      this.setExtraTweets()
      if (this.activeResult != undefined) {
        if (this.activeResult._source.headline == undefined && this.activeResult._source.title == undefined) {
          this.activeResult._source.headline = this.activeResult._source.name
          if (this.highlights) {
            this.activeResult.highlight["headline.@value"] = this.activeResult.highlight["name.@value"]
          }
        }
      }
    },
    currentLang: function(){
      this.fields["@type"]= this.$ml.get('type')
      this.fields["@id"]= this.$ml.get('identifier')
      this.fields.publisher= this.$ml.get('publication')
      this.fields.printEdition= this.$ml.get('edition')
      this.fields.articleSection= this.$ml.get('articlesection')
      this.pagination= this.$ml.get('pagination'),
      this.fields.datePublished=  this.$ml.get('publicationdate')
      this.fields.dateline= this.$ml.get('dateline')
      this.fields.headline= this.$ml.get('title')
      this.fields.creator= this.$ml.get('author')
      this.fields.sender=  this.$ml.get('sender')
      this.fields.recipient=  this.$ml.get('recipient')
      this.fields.description= this.$ml.get('description')
      this.fields.articleBody=  this.$ml.get('text')
      this.fields.text=  this.$ml.get('text')
      this.fields.keywords= this.$ml.get('keywords')
      this.fields.mentions= this.$ml.get('mentions')
      this.fields.duration= this.$ml.get('duration')
//        this.fields.retweeted_tweet= this.$ml.get('retweeted_tweet')
//        this.fields.replied_to_tweet= this.$ml.get('replied_to_tweet')
//        this.fields.quoted_tweet= this.$ml.get('quoted_tweet')
      this.fields.contentUrl= this.$ml.get('content')
      this.fields.about= this.$ml.get('about')
      this.fields.inLanguage= this.$ml.get('language')
      this.fields.contentLocation= this.$ml.get('location')
      this.fields.associatedMedia= this.$ml.get('media')
      this.fields.sameAs= this.$ml.get('link')
      this.fields.url= this.$ml.get('permalink')
      this.fields.provider= this.$ml.get('provider')
      this.fields.sdDatePublished= this.$ml.get('sdDatePublished')
      this.info.content = this.$ml.get('dbinfotooltip')
    }
  },
  created() {
    this.setExtraTweets();
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