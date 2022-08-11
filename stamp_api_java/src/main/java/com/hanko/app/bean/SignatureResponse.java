package com.hanko.app.bean;

import java.util.ArrayList;

public class SignatureResponse {

	/** The pdfs. */
	private ArrayList<PdfResponse> data;

	/**
	 * true or false
	 */
	private boolean success;

	/**
	 * Getter data
	 * @return
	 */
	public ArrayList<PdfResponse> getData() {
		return data;
	}
	/**
	 * Setter data
	 * @param pdf_data
	 */
	public void setData(ArrayList<PdfResponse> pdf_data) {
		this.data = pdf_data;
	}

	/**
	 * Getter success
	 * @return
	 */
	public boolean isSuccess() {
		return success;
	}

	/**
	 * Setter success
	 * @param success
	 */
	public void setSuccess(boolean success) {
		this.success = success;
	}
}
