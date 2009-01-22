/* AbstractWizardFrame - AbstractWizardFrame.java
 * 
 * Base class to create wizard frames in carcara.
 * 
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 * 
 * This product includes software developed by the Fusebox Corporation 
 * (http://www.fusebox.org/).
 * 
 * The Original Code is Carcara "a Candango Modeling tool to create 
 * myFuses/iflux applications" part .
 * 
 * The Initial Developer of the Original Code is Flavio Goncalves Garcia.
 * Portions created by Flavio Goncalves Garcia are Copyright (C) 2008 - 2009.
 * All Rights Reserved.
 * 
 * Contributor(s): Flavio Goncalves Garcia.
 */
package org.candango.carcara.ui.wizard;

import javax.swing.JFrame;

/**
 * Base class to create wizard frames in carcara.
 *
 * @author     Flavio Goncalves Garcia <flavio.garcia at candango.org>
 * @copyright  Copyright (c) 2008 - 2009 Candango Open Source Group
 * @link       http://www.candango.org/myfuses
 * @license    http://www.mozilla.org/MPL/MPL-1.1.html  MPL 1.1
 * @version    SVN: $Id: Project.java 23 2008-12-07 02:54:38Z flavio.garcia $
 * @since 0.0.1
 */
public class AbstractWizardFrame extends JFrame {

	/**
	 * Generated serial version UID 
	 */
	private static final long serialVersionUID = 5771792647335637156L;
	
	/**
	 * Constructor with a title only
	 * 
	 * @param title
	 */
	public AbstractWizardFrame( String title ) {
		super( title );
	}
	
	/**
	 * Construction with a title and wizard instruction
	 * 
	 * @param title
	 * @param instruction
	 */
	public AbstractWizardFrame( String title, String instruction ) {
		this( title );
	}
	
}
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */