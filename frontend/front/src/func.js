export const func = {
    getColorName: (code) => {
        const colorNames = new Map([
            ["#CD5C5C","INDIANRED"],
            ["#F08080","LIGHTCORAL"],
            ["#FA8072","SALMON"],
            ["#E9967A","DARKSALMON"],
            ["#FFA07A","LIGHTSALMON"],
            ["#DC143C","CRIMSON"],
            ["#FF0000","RED"],
            ["#8B0000","DARKRED"],
            ["#FFC0CB","PINK"],
            ["#FFB6C1","LIGHTPINK"],
            ["#FF69B4","HOTPINK"],
            ["#FF1493","DEEPPINK"],
            ["#C71585","MEDIUMVIOLETRED"],
            ["#DB7093","PALEVIOLETRED"],
            ["#FF7F50","CORAL"],
            ["#FF6347","TOMATO"],
            ["#FF4500","ORANGERED"],
            ["#FF8C00","DARKORANGE"],
            ["#FFA500","ORANGE"],
            ["#FFD700","GOLD"],
            ["#FFFF00","YELLOW"],
            ["#FFFFE0","LIGHTYELLOW"],
            ["#FFFACD","LEMONCHIFFON"],
            ["#FAFAD2","LIGHTGOLDENRODYELLOW"],
            ["#FFEFD5","PAPAYAWHIP"],
            ["#FFE4B5","MOCCASIN"],
            ["#FFDAB9","PEACHPUFF"],
            ["#EEE8AA","PALEGOLDENROD"],
            ["#F0E68C","KHAKI"],
            ["#BDB76B","DARKKHAKI"],
            ["#E6E6FA","LAVENDER"],
            ["#D8BFD8","THISTLE"],
            ["#DDA0DD","PLUM"],
            ["#EE82EE","VIOLET"],
            ["#DA70D6","ORCHID"],
            ["#FF00FF","FUCHSIA"],
            ["#FF00FF","MAGENTA"],
            ["#BA55D3","MEDIUMORCHID"],
            ["#9370DB","MEDIUMPURPLE"],
            ["#663399","REBECCAPURPLE"],
            ["#8A2BE2","BLUEVIOLET"],
            ["#9400D3","DARKVIOLET"],
            ["#9932CC","DARKORCHID"],
            ["#8B008B","DARKMAGENTA"],
            ["#800080","PURPLE"],
            ["#4B0082","INDIGO"],
            ["#6A5ACD","SLATEBLUE"],
            ["#483D8B","DARKSLATEBLUE"],
            ["#7B68EE","MEDIUMSLATEBLUE"],
            ["#ADFF2F","GREENYELLOW"],
            ["#7FFF00","CHARTREUSE"],
            ["#7CFC00","LAWNGREEN"],
            ["#00FF00","LIME"],
            ["#32CD32","LIMEGREEN"],
            ["#98FB98","PALEGREEN"],
            ["#90EE90","LIGHTGREEN"],
            ["#00FA9A","MEDIUMSPRINGGREEN"],
            ["#00FF7F","SPRINGGREEN"],
            ["#3CB371","MEDIUMSEAGREEN"],
            ["#2E8B57","SEAGREEN"],
            ["#228B22","FORESTGREEN"],
            ["#008000","GREEN"],
            ["#006400","DARKGREEN"],
            ["#9ACD32","YELLOWGREEN"],
            ["#6B8E23","OLIVEDRAB"],
            ["#6B8E23","OLIVE"],
            ["#556B2F","DARKOLIVEGREEN"],
            ["#66CDAA","MEDIUMAQUAMARINE"],
            ["#8FBC8B","DARKSEAGREEN"],
            ["#20B2AA","LIGHTSEAGREEN"],
            ["#008B8B","DARKCYAN"],
            ["#008080","TEAL"],
            ["#00FFFF","AQUA"],
            ["#00FFFF","CYAN"],
            ["#E0FFFF","LIGHTCYAN"],
            ["#AFEEEE","PALETURQUOISE"],
            ["#7FFFD4","AQUAMARINE"],
            ["#40E0D0","TURQUOISE"],
            ["#48D1CC","MEDIUMTURQUOISE"],
            ["#00CED1","DARKTURQUOISE"],
            ["#5F9EA0","CADETBLUE"],
            ["#4682B4","STEELBLUE"],
            ["#B0C4DE","LIGHTSTEELBLUE"],
            ["#B0E0E6","POWDERBLUE"],
            ["#ADD8E6","LIGHTBLUE"],
            ["#87CEEB","SKYBLUE"],
            ["#87CEFA","LIGHTSKYBLUE"],
            ["#00BFFF","DEEPSKYBLUE"],
            ["#1E90FF","DODGERBLUE"],
            ["#6495ED","CORNFLOWERBLUE"],
            ["#4169E1","ROYALBLUE"],
            ["#0000FF","BLUE"],
            ["#0000CD","MEDIUMBLUE"],
            ["#00008B","DARKBLUE"],
            ["#00008B","NAVY"],
            ["#191970","MIDNIGHTBLUE"],
            ["#FFF8DC","CORNSILK"],
            ["#FFEBCD","BLANCHEDALMOND"],
            ["#FFE4C4","BISQUE"],
            ["#FFDEAD","NAVAJOWHITE"],
            ["#F5DEB3","WHEAT"],
            ["#DEB887","BURLYWOOD"],
            ["#D2B48C","TAN"],
            ["#BC8F8F","ROSYBROWN"],
            ["#F4A460","SANDYBROWN"],
            ["#DAA520","GOLDENROD"],
            ["#B8860B","DARKGOLDENROD"],
            ["#CD853F","PERU"],
            ["#D2691E","CHOCOLATE"],
            ["#8B4513","SADDLEBROWN"],
            ["#A0522D","SIENNA"],
            ["#A52A2A","BROWN"],
            ["#800000","MAROON"],
            ["#FFFFFF","WHITE"],
            ["#FFFAFA","SNOW"],
            ["#F0FFF0","HONEYDEW"],
            ["#F5FFFA","MINTCREAM"],
            ["#F0FFFF","AZURE"],
            ["#F0F8FF","ALICEBLUE"],
            ["#F8F8FF","GHOSTWHITE"],
            ["#F5F5F5","WHITESMOKE"],
            ["#FFF5EE","SEASHELL"],
            ["#F5F5DC","BEIGE"],
            ["#FDF5E6","OLDLACE"],
            ["#FDF5E6","FLORALWHITE"],
            ["#FFFFF0","IVORY"],
            ["#FAEBD7","ANTIQUEWHITE"],
            ["#FAF0E6","LINEN"],
            ["#FFF0F5","LAVENDERBLUSH"],
            ["#FFE4E1","MISTYROSE"],
            ["#DCDCDC","GAINSBORO"],
            ["#D3D3D3","LIGHTGRAY"],
            ["#C0C0C0","SILVER"],
            ["#A9A9A9","DARKGRAY"],
            ["#808080","GRAY"],
            ["#696969","DIMGRAY"],
            ["#778899","LIGHTSLATEGRAY"],
            ["#708090","SLATEGRAY"],
            ["#2F4F4F","DARKSLATEGRAY"],
            ["#000000","BLACK"],
            ["#8FBC8F","DarkSeaGreen"],
            ["#FFFAF0","FloralWhite"],
            ["#B22222","Firebrick"],
            ["#808000","Olive"]
        ])
        const color = colorNames.get(code)
        if (color == undefined) {
            return code
        } else {
            const words = color.toLowerCase().split(" ");
            for (let i = 0; i < words.length; i++) {
                words[i] = words[i][0].toUpperCase() + words[i].substr(1);
            }
            return words.join(" ");
        }
    },
    similarity(s1, s2) {

        function editDistance(s1, s2) {
            s1 = s1.toLowerCase();
            s2 = s2.toLowerCase();
      
            var costs = new Array();
            for (var i = 0; i <= s1.length; i++) {
              var lastValue = i;
              for (var j = 0; j <= s2.length; j++) {
                if (i == 0)
                  costs[j] = j;
                else {
                  if (j > 0) {
                    var newValue = costs[j - 1];
                    if (s1.charAt(i - 1) != s2.charAt(j - 1))
                      newValue = Math.min(Math.min(newValue, lastValue),
                        costs[j]) + 1;
                    costs[j - 1] = lastValue;
                    lastValue = newValue;
                  }
                }
              }
              if (i > 0)
                costs[s2.length] = lastValue;
            }
            return costs[s2.length];
          }

        var longer = s1;
        var shorter = s2;
        if (s1.length < s2.length) {
          longer = s2;
          shorter = s1;
        }
        var longerLength = longer.length;
        if (longerLength == 0) {
          return 1.0;
        }
        return (longerLength - editDistance(longer, shorter)) / parseFloat(longerLength);
      }
}
