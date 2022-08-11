package com.hanko.app.bean;

/**
 * The Class StampRequest.
 *
 * @author hp
 */
public class StampRequest {

	/** 捺印されたページの番号  */
	private int page;

	/** base64形式の印面データ */
	private String stamp_data;

	/** image rotated */
	private Boolean stamp_data_rotated;

	/** 捺印されたX軸 */
	private float x_axis;

	/** 捺印されたY軸 */
	private float y_axis;

	/** 印面の横幅 */
	private float width;

	/** 印面の縦幅 */
	private float height;

	/** Stamp rotate */
	private Float rotateAngle;

	/** 印面のURL */
	private String stamp_url;

	/** メールアドレス */
	private String email;

	/** 捺印日時 */
	private String stamp_time;

	/** ファイル名 */
	private String file_name;

	/** 捺印シリアル */
	private String serial;

	/** 注釈フラグ */
	private int pdf_annotation_flg;

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
	 * Gets the stamp data.
	 *
	 * @return the stamp data
	 */
	public String getStamp_data() {
		return stamp_data;
	}

	/**
	 * Sets the stamp data.
	 *
	 * @param stamp_data the new stamp data
	 */
	public void setStamp_data(String stamp_data) {
		this.stamp_data = stamp_data;
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
	 * Gets the width.
	 *
	 * @return the width
	 */
	public float getWidth() {
		return width;
	}

	/**
	 * Sets the width.
	 *
	 * @param width the new width
	 */
	public void setWidth(float width) {
		this.width = width;
	}

	/**
	 * Gets the height.
	 *
	 * @return the height
	 */
	public float getHeight() {
		return height;
	}

	/**
	 * Sets the height.
	 *
	 * @param height the new height
	 */
	public void setHeight(float height) {
		this.height = height;
	}

	/**
	 * Gets the stamp url.
	 *
	 * @return the stamp url
	 */
	public String getStamp_url() {
		return stamp_url;
	}

	/**
	 * Sets the stamp url.
	 *
	 * @param stamp_url the new stamp url
	 */
	public void setStamp_url(String stamp_url) {
		this.stamp_url = stamp_url;
	}

	/**
	 * Gets the email.
	 *
	 * @return the email
	 */
	public String getEmail() {
		return email;
	}

	/**
	 * Sets the email.
	 *
	 * @param email the new email
	 */
	public void setEmail(String email) {
		this.email = email;
	}

	/**
	 * Gets the stamp_time.
	 *
	 * @return the stamp_time
	 */
	public String getStamp_time() {
		return stamp_time;
	}

	/**
	 * Sets the stamp_time.
	 *
	 * @param stamp_time the new stamp_time
	 */
	public void setStamp_time(String stamp_time) {
		this.stamp_time = stamp_time;
	}

	/**
	 * Gets the file_name.
	 *
	 * @return the file_name
	 */
	public String getFile_name() {
		return file_name;
	}

	/**
	 * Sets the file_name.
	 *
	 * @param file_name the new file_name
	 */
	public void setFile_name(String file_name) {
		this.file_name = file_name;
	}

	/**
	 * Gets the serial.
	 *
	 * @return the serial
	 */
	public String getSerial() {
		return serial;
	}

	/**
	 * Sets the serial.
	 *
	 * @param serial the new serial
	 */
	public void setSerial(String serial) {
		this.serial = serial;
	}

	/**
	 * Gets the pdf_annotation_flg.
	 *
	 * @return the pdf_annotation_flg
	 */
	public int getPdf_annotation_flg() {
		return pdf_annotation_flg;
	}

	/**
	 * Sets the pdf_annotation_flg.
	 *
	 * @param pdf_annotation_flg the new pdf_annotation_flg
	 */
	public void setPdf_annotation_flg(int pdf_annotation_flg) {
		this.pdf_annotation_flg = pdf_annotation_flg;
	}

	/**
	 * Gets the rotateAngle.
	 *
	 * @return the rotateAngle
	 */
	public Float getRotateAngle() {
		return rotateAngle;
	}

	/**
	 * Sets the rotateAngle.
	 *
	 * @param rotateAngle the new rotate angle
	 */
	public void setRotateAngle(Float rotateAngle) {
		this.rotateAngle = rotateAngle;
	}

	/**
	 * Gets the stamp_data_rotated.
	 *
	 * @return the stamp_data_rotated
	 */
	public Boolean getStamp_data_rotated() {
		return stamp_data_rotated;
	}

	/**
	 * Sets the stamp_data_rotated.
	 *
	 * @param stamp_data_rotated the new stamp_data_rotated
	 */
	public void setStamp_data_rotated(Boolean stamp_data_rotated) {
		this.stamp_data_rotated = stamp_data_rotated;
	}


}
