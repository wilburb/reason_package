/*-----------------------------------------------------------------

GEM - Graphics Environment for Multimedia

Load an ARB vertex program/shader
 
 *  Created by tigital on 10/16/04.
 *  Copyright 2004 tigital.
 
Copyright (c) 1997-1999 Mark Danks. mark@danks.org
Copyright (c) G�nther Geiger. geiger@epy.co.at
Copyright (c) 2001-2002 IOhannes m zmoelnig. forum::f�r::uml�ute. IEM. zmoelnig@iem.kug.ac.at
For information on usage and redistribution, and for a DISCLAIMER OF ALL
WARRANTIES, see the file, "GEM.LICENSE.TERMS" in this distribution.

-----------------------------------------------------------------*/

#ifndef INCLUDE_VERTEX_PROGRAM_H_
#define INCLUDE_VERTEX_PROGRAM_H_

#include "Base/GemBase.h"

#define GEM_PROGRAM_none 0
#define GEM_PROGRAM_NV  1
#define GEM_PROGRAM_ARB 2


/*-----------------------------------------------------------------
  -------------------------------------------------------------------
  CLASS
  vertex_program
    
  Loads in a vertex program/shader
    
  KEYWORDS
  
    
  DESCRIPTION

  -----------------------------------------------------------------*/
class GEM_EXTERN vertex_program : public GemBase
{
  CPPEXTERN_HEADER(vertex_program, GemBase)
    
    public:
  
  //////////
  // Constructor
  vertex_program(void);
  vertex_program(t_symbol *filename);

 protected:
    
  //////////
  // Destructor
  virtual ~vertex_program();

  //////////
  // close the program file
  virtual void closeMess(void);
  //////////
  // open a program up
  virtual void openMess(t_symbol *filename);


  //////////
  // which Program do we have (ARB, NV,...)
  virtual GLint queryProgramtype(char*program);

  //////////
  // load the program string to the openGL-engine
  virtual void LoadProgram();

  //////////
  // Do the rendering
  virtual void render(GemState *state);


  //////////
  // Clear the dirty flag on the pixBlock
  virtual void postrender(GemState *state);

  //////////
  virtual void startRendering();

  //////////
  // Print Info about Hardware limits
  virtual void printInfo();

  //////////
  //
  GLuint        m_programType;

  GLuint 	m_programTarget;
  GLuint        m_programID;
  char*		m_programString;
  int		m_size;

 protected:
	
  //////////
  // static member functions
  static void openMessCallback   (void *data, t_symbol *filename);
  static void printMessCallback  (void *);
};

#endif	// for header file
