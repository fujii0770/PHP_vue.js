package com.hanko.app.bean;

import com.itextpdf.kernel.font.PdfFont;

// TODO: Auto-generated Javadoc
/**
 * The Class StampRequest.
 *
 * @author hp
 */
public class TextRequest {

	/** 捺印されたページの番号  */
	private int page;

	/** text */
	private String text;

	/** 捺印されたX軸 */
	private float x_axis;

	/** 捺印されたY軸 */
	private float y_axis;

	/** font size */
	private float fontSize;

	/** font family */
	private String fontFamily;

	private PdfFont font;

	/** font Color **/
	private String fontColor;

	/**
	 * Gets the page.
	 *
	 * @return the page
	 */
	public int getPage() {
		return page;
	}

	/**
	 * Sets the page.
	 *
	 * @param page the new page
	 */
	public void setPage(int page) {
		this.page = page;
	}

	/**
	 * Gets the text.
	 *
	 * @return the text
	 */
	public String getText() {
		return text;
	}

	/**
	 * Sets the text.
	 *
	 * @param text the new text
	 */
	public void setStamp_data(String text) {
		this.text = text;
	}

	/**
	 * Gets the x axis.
	 *
	 * @return the x axis
	 */
	public float getX_axis() {
		return x_axis;
	}

	/**
	 * Sets the x axis.
	 *
	 * @param x_axis the new x axis
	 */
	public void setX_axis(float x_axis) {
		this.x_axis = x_axis;
	}

	/**
	 * Gets the y axis.
	 *
	 * @return the y axis
	 */
	public float getY_axis() {
		return y_axis;
	}

	/**
	 * Sets the y axis.
	 *
	 * @param y_axis the new y axis
	 */
	public void setY_axis(float y_axis) {
		this.y_axis = y_axis;
	}

	/**
	 * Gets the fontSize.
	 *
	 * @return the fontSize
	 */
	public float getFontSize() {
		return fontSize;
	}

	/**
	 * Sets the fontSize.
	 *
	 * @param fontSize the new fontSize
	 */
	public void setFontSize(float fontSize) {
		this.fontSize = fontSize;
	}

	/**
	 * Gets the fontFamily.
	 *
	 * @return the fontFamily
	 */
	public String getFontFamily() {
		return fontFamily;
	}

	/**
	 * Sets the fontFamily.
	 *
	 * @param height the new fontFamily
	 */
	public void setFontFamily(String fontFamily) {
		this.fontFamily = fontFamily;
	}

	public PdfFont getFont() {
		return font;
	}

	public void setFont(PdfFont font) {
		this.font = font;
	}

	public void setFontColor(String fontColor) {
		this.fontColor = fontColor;
	}

	public String getFontColor() {
		return this.fontColor;
	}

}
