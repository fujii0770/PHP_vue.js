package com.hanko.app.utils;

import com.itextpdf.kernel.colors.Color;
import com.itextpdf.kernel.colors.DeviceRgb;

import java.util.regex.Pattern;

public class ConvertColorValue {

    private static String s = "0123456789ABCDEF";

    /**
     *
     *
     * @param hex
     * @return
     */
    public static Color ConvertHexToRGB(String hex) {
        if (hex == null || "".equals(hex)) {
            return new DeviceRgb(0,0,0);
        }
        String rgb = "";
        String regex = "^[0-9A-F]{3}|[0-9A-F]{6}$";
        if (hex != null) {
            hex = hex.toUpperCase();
            if (hex.substring(0, 1).equals("#")) {
                hex = hex.substring(1);
            }
            if (Pattern.compile(regex).matcher(hex).matches()) {
                String a, c, d;
                String[] str = new String[3];
                for (int i = 0; i < 3; i++) {
                    a = hex.length() == 6 ? hex.substring(i * 2, i * 2 + 2) : hex.substring(i, i + 1) + hex.substring(i, i + 1);
                    c = a.substring(0, 1);
                    d = a.substring(1, 2);
                    str[i] = String.valueOf(s.indexOf(c) * 16 + s.indexOf(d));
                }
                return new DeviceRgb(Integer.parseInt(str[0]),Integer.parseInt(str[1]),Integer.parseInt(str[2]));
            }
        }

        return new DeviceRgb(0,0,0);
    }

}
