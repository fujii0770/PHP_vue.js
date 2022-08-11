package com.hanko.app.bean;

import java.util.ArrayList;

/**
 * The Class PdfRequest.
 *
 * @author hp
 */
public class PdfRequest {

	/** The circular document id. */
	private Integer circular_document_id;

	/**  base64形式のPDFデータ. */
	private String pdf_data;

	/**  base64形式のPDFデータ. */
	private String append_pdf_data;

	/**  0:無効   1：有効. */
	private boolean usingTas;

	/** The stamps. */
	private ArrayList<StampRequest> stamps;

	/** The texts. */
	private ArrayList<TextRequest> texts;

	private boolean usingDTS;

	/**
	 * Gets the stamps.
	 *
	 * @return the stamps
	 */
	public ArrayList<StampRequest> getStamps() {
		return stamps;
	}

	/**
	 * Sets the stamps.
	 *
	 * @param stamps the new stamps
	 */
	public void setStamps(ArrayList<StampRequest> stamps) {
		this.stamps = stamps;
	}

	/**
	 * Gets the pdf data.
	 *
	 * @return the pdf data
	 */
	public String getPdf_data() {
		return pdf_data;
	}

	/**
	 * Sets the circular document id.
	 *
	 * @param circular document id the id of file
	 */
	public void setCircular_document_id(Integer circular_document_id) {
		this.circular_document_id = circular_document_id;
	}

	/**
	 * Gets the circular document id.
	 *
	 * @return the circular document id
	 */
	public Integer getCircular_document_id() {
		return circular_document_id;
	}

	/**
	 * Sets the pdf data.
	 *
	 * @param pdf_data the new pdf data
	 */
	public void setPdf_data(String pdf_data) {
		this.pdf_data = pdf_data;
	}

	/**
	 * Gets the texts.
	 * @return texts
	 */
	public ArrayList<TextRequest> getTexts() {
		return texts;
	}

	/**
	 * Sets the texts.
	 * @param texts
	 */
	public void setTexts(ArrayList<TextRequest> texts) {
		this.texts = texts;
	}

	/**
	 * Gets the append_pdf_data.
	 * @return append_pdf_data
	 */
	public String getAppend_pdf_data() {
		return append_pdf_data;
	}

	/**
	 * Sets the append_pdf_data.
	 * @param texts
	 */
	public void setAppend_pdf_data(String append_pdf_data) {
		this.append_pdf_data = append_pdf_data;
	}

	/**
	 * Gets the usingTas.
	 *
	 * @return the usingTas
	 */
	public boolean isUsingTas() {
		return usingTas;
	}

	/**
	 * Sets the usingTas.
	 *
	 * @param usingTas the new usingTas
	 */
	public void setUsingTas(boolean usingTas) {
		this.usingTas = usingTas;
	}

	/**
	 * Gets the usingDTS.
	 *
	 * @return the usingDTS
	 */
	public boolean isUsingDTS() {
		return usingDTS;
	}

	/**
	 * Sets the usingDTS.
	 *
	 * @param usingDTS the new usingDTS
	 */
	public void setUsingDTS(boolean usingDTS) {
		this.usingDTS = usingDTS;
	}
}
